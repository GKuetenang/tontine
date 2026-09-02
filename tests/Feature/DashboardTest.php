<?php

use App\Actions\Dashboard\BuildUserDashboardAction;
use App\Enums\LoanStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows onboarding when the user has no group', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('has_groups', false)
            ->where('summary.groups_count', 0)
            ->has('next_meetings', 0)
            ->has('recent_transactions', 0));
});

it('builds a personal dashboard without mixing another members finances', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $group = Group::factory()->create(['currency' => 'XAF']);
    $membership = Membership::factory()->active()->for($user)->for($group)->create();
    $otherMembership = Membership::factory()->active()->for($otherUser)->for($group)->create();
    $session = Session::factory()->active()->for($group)->create();
    $participant = SessionParticipant::factory()->for($session)->for($membership)->create();
    $meeting = Meeting::factory()->for($session)->create([
        'number' => 1,
        'scheduled_at' => now()->addDay(),
    ]);
    $contribution = Contribution::factory()->for($meeting)->for($participant, 'sessionParticipant')->create([
        'amount_due' => 10_000,
    ]);
    Transaction::factory()->for($session)->for($membership)->for($contribution, 'transactionable')->create([
        'type' => TransactionType::Contribution,
        'direction' => TransactionDirection::Credit,
        'amount' => '2500.00',
    ]);
    Transaction::factory()->for($session)->for($otherMembership)->create(['amount' => '99999.00']);
    Loan::factory()->for($session)->for($membership)->create([
        'status' => LoanStatus::Active,
        'total_due' => '12000.00',
    ]);

    $dashboard = app(BuildUserDashboardAction::class)->execute($user);

    expect($dashboard['summary']['groups_count'])->toBe(1)
        ->and($dashboard['summary']['upcoming_meetings_count'])->toBe(1)
        ->and($dashboard['summary']['contributions_due'])->toBe([
            ['currency' => 'XAF', 'amount' => '7500.00'],
        ])
        ->and($dashboard['summary']['loans_due'])->toBe([
            ['currency' => 'XAF', 'amount' => '12000.00'],
        ])
        ->and($dashboard['recent_transactions'])->toHaveCount(1);
});

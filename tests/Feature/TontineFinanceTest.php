<?php

use App\Actions\Finances\BuildTontineFinancialDashboardAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\LoanStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Meeting;
use App\Models\Membership;
use App\Models\Repayment;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('builds the consolidated financial state from transactions without floats', function (): void {
    $tontine = Tontine::factory()->create();
    $session = Session::factory()->for($tontine)->create();

    foreach ([
        [TransactionType::Contribution, TransactionDirection::Credit, '10000.50'],
        [TransactionType::Insurance, TransactionDirection::Credit, '2000.25'],
        [TransactionType::Repayment, TransactionDirection::Credit, '1000.00'],
        [TransactionType::Loan, TransactionDirection::Debit, '5000.00'],
        [TransactionType::Donation, TransactionDirection::Debit, '500.50'],
        [TransactionType::Payout, TransactionDirection::Debit, '2000.00'],
    ] as [$type, $direction, $amount]) {
        Transaction::factory()->for($session)->create(compact('type', 'direction', 'amount'));
    }

    $membership = Membership::factory()->active()->create(['tontine_id' => $tontine->id]);
    $loan = Loan::factory()->for($session)->for($membership)->create([
        'status' => LoanStatus::Active,
        'total_due' => '6000.00',
    ]);
    Repayment::query()->forceCreate([
        'loan_id' => $loan->id,
        'amount' => '1000.00',
        'interest_amount' => '500.00',
        'principal_amount' => '500.00',
        'paid_at' => now(),
        'created_by' => User::factory()->create()->id,
    ]);

    $dashboard = app(BuildTontineFinancialDashboardAction::class)->execute($tontine);

    expect($dashboard['summary'])->toBe([
        'credits' => '13000.75',
        'debits' => '7500.50',
        'balance' => '5500.25',
        'outstanding_loans' => '5000.00',
    ])->and($dashboard['recent_transactions'])->toHaveCount(6)
        ->and(collect($dashboard['breakdown'])->firstWhere('type', 'contribution')['credits'])
        ->toBe('10000.50');
});

it('allows an authorized president to view the tontine finances', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->create();
    $meeting = Meeting::factory()->for($session)->create(['number' => 1]);
    Transaction::factory()->for($session)->create([
        'direction' => TransactionDirection::Credit,
        'amount' => '1250.50',
    ]);

    $this->actingAs($president)
        ->get(route('tontines.finances.index', $tontine))
        ->assertInertia(fn (Assert $page) => $page
            ->component('finances/index')
            ->where('dashboard.summary.balance', '1250.50')
            ->has('dashboard.breakdown', count(TransactionType::cases()))
            ->has('dashboard.recent_transactions', 1)
            ->where('sessions.0.meetings.0.value', (string) $meeting->id));
});

it('filters financial movements by a meeting within the selected session', function (): void {
    $tontine = Tontine::factory()->create();
    $session = Session::factory()->for($tontine)->create();
    $firstMeeting = Meeting::factory()->for($session)->create(['number' => 1]);
    $secondMeeting = Meeting::factory()->for($session)->create(['number' => 2]);

    foreach ([[$firstMeeting, '1500.25'], [$secondMeeting, '750.50']] as [$meeting, $amount]) {
        $contribution = Contribution::factory()->for($meeting)->create();
        Transaction::factory()
            ->for($session)
            ->for($contribution, 'transactionable')
            ->create([
                'type' => TransactionType::Contribution,
                'direction' => TransactionDirection::Credit,
                'amount' => $amount,
            ]);
    }

    $dashboard = app(BuildTontineFinancialDashboardAction::class)->execute($tontine, [
        'session_id' => $session->id,
        'meeting_id' => $firstMeeting->id,
    ]);

    expect($dashboard['summary']['credits'])->toBe('1500.25')
        ->and($dashboard['summary']['balance'])->toBe('1500.25')
        ->and($dashboard['recent_transactions'])->toHaveCount(1);
});

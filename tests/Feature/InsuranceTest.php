<?php

use App\Actions\Insurance\CreateInsuranceContributionAction;
use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a member insurance contribution with exactly one credit transaction', function (): void {
    $session = Session::factory()->active()->create();
    $membership = Membership::factory()->active()->create(['tontine_id' => $session->tontine_id]);
    SessionParticipant::factory()->for($session)->for($membership)->create();
    $actor = User::factory()->create();

    $contribution = app(CreateInsuranceContributionAction::class)->execute(
        session: $session,
        membership: $membership,
        actor: $actor,
        amount: '1250.50',
        description: 'Achat de fournitures',
        occurredAt: '2026-08-31 10:30:00',
    );

    $transaction = Transaction::query()->sole();

    expect($contribution->amount)->toBe('1250.50')
        ->and($contribution->membership_id)->toBe($membership->id)
        ->and($transaction->type)->toBe(TransactionType::Insurance)
        ->and($transaction->direction)->toBe(TransactionDirection::Credit)
        ->and($transaction->amount)->toBe('1250.50')
        ->and($transaction->session_id)->toBe($session->id)
        ->and($transaction->membership_id)->toBe($membership->id)
        ->and($transaction->transactionable->is($contribution))->toBeTrue()
        ->and($contribution->transactions()->count())->toBe(1);
});

it('shows the insurance total and contributions to an authorized user', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $member = User::factory()->create([
        'first_name' => 'Gustave',
        'name' => 'Kamga',
    ]);
    $membership = app(CreateMembershipAction::class)->execute($tontine, $member, 'member');
    $session = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();

    foreach (['5000.00', '1250.50'] as $amount) {
        app(CreateInsuranceContributionAction::class)->execute(
            session: $session,
            membership: $membership,
            actor: $president,
            amount: $amount,
            description: 'Versement assurance',
            occurredAt: now(),
        );
    }

    $this->actingAs($president)
        ->get(route('tontines.sessions.insurance.index', [
            'tontine' => $tontine,
            'session' => $session,
            'q' => 'Gustave',
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('insurance/index')
            ->has('collection.data', 2)
            ->where('collection.data.0.member_name', $member->full_name)
            ->where('summary.total', '6250.50')
            ->where('summary.contributions_count', 2)
            ->where('summary.contributors_count', 1));
});

it('allows an authorized user to record an insurance contribution through the session route', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    $membership = app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.insurance.store', [$tontine, $session]), [
            'membership_id' => $membership->id,
            'amount' => '8500.25',
            'description' => 'Approvisionnement initial',
            'occurred_at' => '2026-08-31',
        ])
        ->assertRedirect();

    expect(InsuranceContribution::query()->count())->toBe(1)
        ->and(Transaction::query()->count())->toBe(1);
});

it('uses the current date and accepts no description for an insurance contribution', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    $membership = app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.insurance.store', [$tontine, $session]), [
            'membership_id' => $membership->id,
            'amount' => '50000.00',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $contribution = InsuranceContribution::query()->sole();

    expect($contribution->description)->toBeNull()
        ->and($contribution->occurred_at)->not->toBeNull()
        ->and($contribution->transactions()->sole()->occurred_at)->not->toBeNull();
});

it('rejects invalid insurance contributions', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    $membership = app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->active()->create();
    SessionParticipant::factory()->for($session)->for($membership)->create();

    $this->actingAs($president)
        ->post(route('tontines.sessions.insurance.store', [$tontine, $session]), [
            'membership_id' => null,
            'amount' => '0',
            'description' => '',
            'occurred_at' => 'not-a-date',
        ])
        ->assertSessionHasErrors(['membership_id', 'amount', 'occurred_at']);

    expect(InsuranceContribution::query()->count())->toBe(0)
        ->and(Transaction::query()->count())->toBe(0);
});

<?php

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Sessions\ActivateSessionAction;
use App\Actions\Sessions\CloseSessionAction;
use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\DeleteSessionAction;
use App\Actions\Sessions\UpdateSessionAction;
use App\Actions\Tontines\CreateDefaultTontineRolesAction;
use App\Enums\SessionStatus;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a session can be created for a tontine', function (): void {
    $tontine = Tontine::factory()->create([
        'default_contribution_amount' => 50_000,
    ]);

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
            'description' => 'Session annuelle',
            'start_at' => '2027-01-01',
            'end_at' => '2027-12-31',
            'beneficiaries_per_meeting' => 3,
        ],
    );

    expect($session)
        ->name->toBe('Session 2027')
        ->slug->toStartWith('session-2027-')
        ->tontine_id->toBe($tontine->id)
        ->description->toBe('Session annuelle')
        ->status->toBe(SessionStatus::Draft)
        ->default_contribution_amount->toBe(50_000)
        ->beneficiaries_per_meeting->toBe(3)
        ->activated_at->toBeNull()
        ->closed_at->toBeNull();

    /** @var TestCase $this */
    $this->assertDatabaseHas('tontine_sessions', [
        'id' => $session->id,
        'tontine_id' => $tontine->id,
        'name' => 'Session 2027',
        'slug' => $session->slug,
        'status' => SessionStatus::Draft->value,
        'default_contribution_amount' => 50_000,
        'activated_at' => null,
        'closed_at' => null,
    ]);
});

test('a session copies the default contribution amount from the tontine', function (): void {
    $tontine = Tontine::factory()->create([
        'default_contribution_amount' => 75_000,
    ]);

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
        ],
    );

    expect($session->default_contribution_amount)
        ->toBe(75_000);
});

test('a session can override the tontine default contribution amount', function (): void {
    $tontine = Tontine::factory()->create([
        'default_contribution_amount' => 50_000,
    ]);

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
            'default_contribution_amount' => 100_000,
        ],
    );

    expect($session->default_contribution_amount)
        ->toBe(100_000);
});

test('generated session slug contains a random suffix', function (): void {
    $tontine = Tontine::factory()->create();

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
        ],
    );

    expect($session->slug)
        ->toStartWith('session-2027-')
        ->toMatch('/^session-2027-[a-z0-9]+$/');
});

test('session slugs are unique inside the same tontine', function (): void {
    $tontine = Tontine::factory()->create();

    $action = app(CreateSessionAction::class);

    $first = $action->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
        ],
    );

    /*
     * Les noms sont différents pour respecter la contrainte unique
     * [tontine_id, name], mais produisent le même slug de base.
     */
    $second = $action->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session-2027',
        ],
    );

    expect($first->slug)
        ->toStartWith('session-2027-')
        ->not->toBe($second->slug)
        ->and($second->slug)
        ->toStartWith('session-2027-');
});

test('the same session name can exist in different tontines', function (): void {
    $firstTontine = Tontine::factory()->create();
    $secondTontine = Tontine::factory()->create();

    $action = app(CreateSessionAction::class);

    $first = $action->execute(
        tontine: $firstTontine,
        attributes: [
            'name' => 'Session 2027',
        ],
    );

    $second = $action->execute(
        tontine: $secondTontine,
        attributes: [
            'name' => 'Session 2027',
        ],
    );

    expect($first->name)
        ->toBe($second->name)
        ->and($first->tontine_id)
        ->not->toBe($second->tontine_id)
        ->and($first->slug)
        ->not->toBe($second->slug);
});

test('updating a draft session does not modify its slug', function (): void {
    $tontine = Tontine::factory()->create();

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
            'description' => 'Description initiale',
            'start_at' => '2027-01-01',
            'end_at' => '2027-12-31',
        ],
    );

    $originalSlug = $session->slug;

    $updatedSession = app(UpdateSessionAction::class)->execute(
        session: $session,
        attributes: [
            'name' => 'Exercice annuel 2027',
            'description' => 'Nouvelle description',
            'start_at' => '2027-02-01',
            'end_at' => '2027-11-30',
            'beneficiaries_per_meeting' => 2,
        ],
    );

    expect($updatedSession)
        ->name->toBe('Exercice annuel 2027')
        ->description->toBe('Nouvelle description')
        ->beneficiaries_per_meeting->toBe(2)
        ->slug->toBe($originalSlug)
        ->status->toBe(SessionStatus::Draft);
});

test('a draft session can be updated through the form endpoint', function (): void {
    app(PermissionSeeder::class)->run();
    $president = User::factory()->create();
    $tontine = Tontine::factory()->create(['user_id' => $president->id]);
    app(CreateDefaultTontineRolesAction::class)->execute($tontine);
    app(CreateMembershipAction::class)->execute($tontine, $president, 'president');
    $session = Session::factory()->for($tontine)->draft()->create([
        'name' => 'Ancien nom',
        'beneficiaries_per_meeting' => 1,
    ]);

    $this->actingAs($president)
        ->put(route('tontines.sessions.update', [$tontine, $session]), [
            'name' => 'Nouveau nom',
            'default_contribution_amount' => 75000,
            'beneficiaries_per_meeting' => 3,
            'draw_allocation_mode' => 'one_per_member',
            'start_at' => '2027-01-01 09:00:00',
            'end_at' => '2027-12-31 09:00:00',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($session->refresh())
        ->name->toBe('Nouveau nom')
        ->default_contribution_amount->toBe(75000)
        ->beneficiaries_per_meeting->toBe(3);
});

test('a closed session cannot be updated', function (): void {
    $session = Session::factory()
        ->closed()
        ->create();

    app(UpdateSessionAction::class)->execute(
        session: $session,
        attributes: [
            'name' => 'Nouveau nom',
            'description' => null,
            'start_at' => null,
            'end_at' => null,
        ],
    );
})->throws(ValidationException::class);

test('a draft session can be activated', function (): void {
    $session = Session::factory()
        ->create([
            'status' => SessionStatus::Draft,
            'default_contribution_amount' => 50_000,
        ]);

    SessionParticipant::factory()
        ->for($session)
        ->create([
            'contribution_amount' => 50_000,
            'draw_entries_count' => 1,
            'is_active' => true,
        ]);

    $activated = app(ActivateSessionAction::class)
        ->execute($session);

    expect($activated)
        ->status->toBe(SessionStatus::Active)
        ->activated_at->not->toBeNull()
        ->closed_at->toBeNull();
});

test('a session cannot be activated while another session is active', function (): void {
    $tontine = Tontine::factory()->create();

    Session::factory()
        ->for($tontine)
        ->active()
        ->create();

    $next = Session::factory()
        ->for($tontine)
        ->draft()
        ->create();

    SessionParticipant::factory()
        ->for($next)
        ->create([
            'contribution_amount' => 50_000,
            'draw_entries_count' => 1,
            'is_active' => true,
        ]);

    app(ActivateSessionAction::class)
        ->execute($next);
})->throws(ValidationException::class);

test('a session without active participants cannot be activated', function (): void {
    $session = Session::factory()
        ->create([
            'status' => SessionStatus::Draft,
        ]);

    app(ActivateSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('a closed session cannot be activated', function (): void {
    $session = Session::factory()
        ->closed()
        ->create();

    app(ActivateSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('an active session can be closed', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    app(CloseSessionAction::class)
        ->execute($session);

    expect($session->refresh())
        ->status->toBe(SessionStatus::Closed)
        ->closed_at->not->toBeNull();
});

test('a draft session cannot be closed', function (): void {
    $session = Session::factory()
        ->create([
            'status' => SessionStatus::Draft,
        ]);

    app(CloseSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('an active session cannot be deleted', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    app(DeleteSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('a closed session cannot be deleted', function (): void {
    $session = Session::factory()
        ->closed()
        ->create();

    app(DeleteSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('an empty draft session can be soft deleted', function (): void {
    $session = Session::factory()
        ->create([
            'status' => SessionStatus::Draft,
        ]);

    app(DeleteSessionAction::class)
        ->execute($session);

    /** @var TestCase $this */
    $this->assertSoftDeleted('tontine_sessions', [
        'id' => $session->id,
    ]);
});

test('a draft session containing participants cannot be deleted', function (): void {
    $session = Session::factory()
        ->create([
            'status' => SessionStatus::Draft,
        ]);

    SessionParticipant::factory()
        ->for($session)
        ->create();

    app(DeleteSessionAction::class)
        ->execute($session);
})->throws(ValidationException::class);

test('session dates are cast to immutable datetime instances', function (): void {
    $session = Session::factory()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-12-31',
    ]);

    expect($session)
        ->start_at->toBeInstanceOf(CarbonImmutable::class)
        ->end_at->toBeInstanceOf(CarbonImmutable::class);
});

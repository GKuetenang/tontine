<?php

use App\Actions\Sessions\ActivateSessionAction;
use App\Actions\Sessions\CloseSessionAction;
use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\DeleteSessionAction;
use App\Actions\Sessions\UpdateSessionAction;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a session can be created for a tontine', function (): void {
    $tontine = Tontine::factory()->create();

    $session = app(CreateSessionAction::class)->execute(
        tontine: $tontine,
        attributes: [
            'name' => 'Session 2027',
            'description' => 'Session annuelle',
            'start_at' => '2027-01-01',
            'end_at' => '2027-12-31',
        ],
    );

    expect($session)
        ->name->toBe('Session 2027')
        ->slug->toStartWith('session-2027-')
        ->tontine_id->toBe($tontine->id)
        ->description->toBe('Session annuelle')
        ->is_active->toBeFalse()
        ->is_closed->toBeFalse()
        ->activated_at->toBeNull()
        ->closed_at->toBeNull();

    $this->assertDatabaseHas('sessions', [
        'id' => $session->id,
        'tontine_id' => $tontine->id,
        'name' => 'Session 2027',
        'slug' => $session->slug,
        'is_active' => false,
        'is_closed' => false,
    ]);
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
        ->toMatch('/^session-2027-[A-Za-z0-9]+$/');
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
     * Le nom doit être différent à cause de la contrainte unique
     * sur [tontine_id, name], tout en produisant le même slug de base.
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

test('updating a session does not modify its slug', function (): void {
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
        ],
    );
    expect($updatedSession)
        ->name->toBe('Exercice annuel 2027')
        ->description->toBe('Nouvelle description')
        ->slug->toBe($originalSlug);
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

test('activating a session deactivates the previous active session', function (): void {
    $tontine = Tontine::factory()->create();

    $previous = Session::factory()
        ->for($tontine)
        ->active()
        ->create();

    $next = Session::factory()
        ->for($tontine)
        ->create([
            'is_active' => false,
            'is_closed' => false,
            'activated_at' => null,
        ]);

    app(ActivateSessionAction::class)->execute($next);

    expect($previous->refresh())
        ->is_active->toBeFalse()
        ->and($next->refresh())
        ->is_active->toBeTrue()
        ->activated_at->not->toBeNull();
});

test('a closed session cannot be activated', function (): void {
    $session = Session::factory()
        ->closed()
        ->create();

    app(ActivateSessionAction::class)->execute($session);
})->throws(ValidationException::class);

test('closing a session deactivates it', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    app(CloseSessionAction::class)->execute($session);

    expect($session->refresh())
        ->is_active->toBeFalse()
        ->is_closed->toBeTrue()
        ->closed_at->not->toBeNull();
});

test('an active session cannot be deleted', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    app(DeleteSessionAction::class)->execute($session);
})->throws(ValidationException::class);

test('a regular open session can be soft deleted', function (): void {
    $session = Session::factory()->create([
        'is_active' => false,
        'is_closed' => false,
    ]);

    app(DeleteSessionAction::class)->execute($session);

    $this->assertSoftDeleted('sessions', [
        'id' => $session->id,
    ]);
});

test('session dates are cast to immutable datetime instances', function (): void {
    $session = Session::factory()->create([
        'start_at' => '2027-01-01',
        'end_at' => '2027-12-31',
    ]);

    expect($session)
        ->start_at->toBeInstanceOf(Carbon\CarbonImmutable::class)
        ->end_at->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

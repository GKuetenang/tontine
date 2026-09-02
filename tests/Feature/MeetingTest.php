<?php

use App\Actions\Meetings\CancelMeetingAction;
use App\Actions\Meetings\CreateMeetingAction;
use App\Actions\Meetings\OpenMeetingAction;
use App\Actions\Meetings\UpdateMeetingAction;
use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('a meeting can be created', function (): void {
    $session = Session::factory()->create();
    $creator = User::factory()->create();

    $scheduledAt = CarbonImmutable::parse(
        '2026-09-15 18:00:00',
    );

    $meeting = app(CreateMeetingAction::class)
        ->execute(
            session: $session,
            creator: $creator,
            title: 'Assise de septembre',
            scheduledAt: $scheduledAt,
            description: 'Assise mensuelle',
            location: 'Salle principale',
        );

    expect($meeting)
        ->title->toBe('Assise de septembre')
        ->number->toBe(1)
        ->status->toBe(MeetingStatus::Scheduled)
        ->created_by->toBe($creator->id)
        ->session_id->toBe($session->id)
        ->opened_at->toBeNull()
        ->closed_at->toBeNull();
});

test('meeting receives the next number in its session', function (): void {
    $session = Session::factory()->create();
    $creator = User::factory()->create();

    app(CreateMeetingAction::class)->execute(
        session: $session,
        creator: $creator,
        title: 'Première assise',
        scheduledAt: now()->toImmutable(),
    );

    $second = app(CreateMeetingAction::class)->execute(
        session: $session,
        creator: $creator,
        title: 'Deuxième assise',
        scheduledAt: now()
            ->addMonth()
            ->toImmutable(),
    );

    expect($second->number)->toBe(2);
});

test('meeting receives a slug that remains unchanged after update', function (): void {
    $session = Session::factory()->create();
    $creator = User::factory()->create();

    $meeting = app(CreateMeetingAction::class)
        ->execute(
            session: $session,
            creator: $creator,
            title: 'Assise septembre',
            scheduledAt: now()->toImmutable(),
        );

    $slug = $meeting->slug;

    app(UpdateMeetingAction::class)->execute(
        meeting: $meeting,
        title: 'Assise extraordinaire',
        scheduledAt: now()
            ->addDay()
            ->toImmutable(),
    );

    expect($meeting->refresh())
        ->title->toBe('Assise extraordinaire')
        ->slug->toBe($slug);
});

test('an in progress meeting cannot be updated', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    app(UpdateMeetingAction::class)->execute(
        meeting: $meeting,
        title: 'Nouveau titre',
        scheduledAt: now()->toImmutable(),
    );
})->throws(ValidationException::class);

test('a scheduled meeting can be cancelled', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $meeting = app(CancelMeetingAction::class)
        ->execute($meeting);

    expect($meeting)
        ->status->toBe(MeetingStatus::Cancelled)
        ->opened_at->toBeNull()
        ->closed_at->toBeNull();
});

test('an in progress meeting cannot be cancelled', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    app(CancelMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a scheduled meeting can be opened', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $participant = SessionParticipant::factory()
        ->for($session)
        ->create([
            'is_active' => true,
        ]);

    $meeting = app(OpenMeetingAction::class)
        ->execute($meeting);

    expect($meeting)
        ->status->toBe(MeetingStatus::InProgress)
        ->opened_at->not->toBeNull()
        ->closed_at->toBeNull();
});

test('opening a meeting creates an attendance for every active participant', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $first = SessionParticipant::factory()
        ->create([
            'session_id' => $session->id,
            'is_active' => true,
        ]);

    $second = SessionParticipant::factory()
        ->create([
            'session_id' => $session->id,
            'is_active' => true,
        ]);

    app(OpenMeetingAction::class)
        ->execute($meeting);

    expect(
        $meeting
            ->attendances()
            ->count(),
    )->toBe(2);

    /** @var TestCase $this */
    $this->assertDatabaseHas(
        'meeting_attendances',
        [
            'meeting_id' => $meeting->id,
            'session_participant_id' => $first->id,
            'status' => AttendanceStatus::Pending->value,
        ],
    );

    /** @var TestCase $this */
    $this->assertDatabaseHas(
        'meeting_attendances',
        [
            'meeting_id' => $meeting->id,
            'session_participant_id' => $second->id,
        ],
    );
});

test('inactive session participants are not added to meeting attendance', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    $activeParticipant =
        SessionParticipant::factory()
            ->create([
                'session_id' => $session->id,
                'is_active' => true,
            ]);

    $inactiveParticipant =
        SessionParticipant::factory()
            ->inactive()
            ->create([
                'session_id' => $session->id,
            ]);

    app(OpenMeetingAction::class)
        ->execute($meeting);

    expect(
        $meeting->attendances()->count(),
    )->toBe(1);

    /** @var TestCase $this */
    $this->assertDatabaseHas(
        'meeting_attendances',
        [
            'meeting_id' => $meeting->id,
            'session_participant_id' => $activeParticipant->id,
        ],
    );

    /** @var TestCase $this */
    $this->assertDatabaseMissing(
        'meeting_attendances',
        [
            'meeting_id' => $meeting->id,
            'session_participant_id' => $inactiveParticipant->id,
        ],
    );
});

test('an in progress meeting cannot be opened again', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    app(OpenMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a meeting cannot be opened when its session is not active', function (): void {
    $session = Session::factory()
        ->draft()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    app(OpenMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('a meeting cannot be opened without active participants', function (): void {
    $session = Session::factory()
        ->active()
        ->create();

    $meeting = Meeting::factory()
        ->for($session)
        ->scheduled()
        ->create();

    app(OpenMeetingAction::class)
        ->execute($meeting);
})->throws(ValidationException::class);

test('contribution keeps the amount due even if participant amount changes later', function (): void {
    [
        $contribution,
    ] = contributionForOpenMeeting(40_000);

    $participant =
        $contribution->sessionParticipant;

    $participant->update([
        'contribution_amount' => 70_000,
    ]);

    expect($contribution->refresh()->amount_due)
        ->toBe(40_000);
});

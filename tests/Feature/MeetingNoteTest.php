<?php

use App\Actions\MeetingNotes\AddMeetingNoteAction;
use App\Actions\MeetingNotes\DeleteMeetingNoteAction;
use App\Actions\MeetingNotes\UpdateMeetingNoteAction;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a note can be added during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $creator = User::factory()->create();

    $note = app(AddMeetingNoteAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            content: 'Les cotisations ont été discutées.',
        );

    expect($note)
        ->meeting_id->toBe($meeting->id)
        ->created_by->toBe($creator->id)
        ->content->toBe(
            'Les cotisations ont été discutées.',
        );
});

test('a note stores its creator', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $creator = User::factory()->create();

    $note = app(AddMeetingNoteAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            content: 'Compte rendu d’assise.',
        );

    expect($note->creator)
        ->not->toBeNull()
        ->id->toBe($creator->id);
});

test('a note cannot be added before meeting opens', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $creator = User::factory()->create();

    app(AddMeetingNoteAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            content: 'Note impossible.',
        );
})->throws(ValidationException::class);

test('a note cannot be added after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $creator = User::factory()->create();

    app(AddMeetingNoteAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            content: 'Note impossible.',
        );
})->throws(ValidationException::class);

test('a note can be updated during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $note = MeetingNote::factory()
        ->for($meeting)
        ->create([
            'content' => 'Ancienne note',
        ]);

    $note = app(UpdateMeetingNoteAction::class)
        ->execute(
            note: $note,
            content: 'Nouvelle note',
        );

    expect($note->content)
        ->toBe('Nouvelle note');
});

test('a note cannot be updated after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $note = MeetingNote::factory()
        ->for($meeting)
        ->create();

    app(UpdateMeetingNoteAction::class)
        ->execute(
            note: $note,
            content: 'Modification interdite',
        );
})->throws(ValidationException::class);

test('a note can be deleted during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $note = MeetingNote::factory()
        ->for($meeting)
        ->create();

    app(DeleteMeetingNoteAction::class)
        ->execute($note);

    expect(
        MeetingNote::query()
            ->whereKey($note->id)
            ->exists(),
    )->toBeFalse();
});

test('a note cannot be deleted after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $note = MeetingNote::factory()
        ->for($meeting)
        ->create();

    app(DeleteMeetingNoteAction::class)
        ->execute($note);
})->throws(ValidationException::class);

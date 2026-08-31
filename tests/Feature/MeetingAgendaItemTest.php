<?php

use App\Actions\MeetingAgendaItems\AddMeetingAgendaItemAction;
use App\Actions\MeetingAgendaItems\RemoveMeetingAgendaItemAction;
use App\Actions\MeetingAgendaItems\ReorderMeetingAgendaItemsAction;
use App\Models\Meeting;
use Illuminate\Validation\ValidationException;

test('an agenda item can be added to a scheduled meeting', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $item = app(AddMeetingAgendaItemAction::class)
        ->execute(
            meeting: $meeting,
            title: 'Cotisations',
            description: 'Collecte des cotisations',
        );

    expect($item)
        ->title->toBe('Cotisations')
        ->position->toBe(1)
        ->meeting_id->toBe($meeting->id);
});

test('agenda items receive sequential positions', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    app(AddMeetingAgendaItemAction::class)
        ->execute(
            meeting: $meeting,
            title: 'Premier point',
        );

    $second = app(AddMeetingAgendaItemAction::class)
        ->execute(
            meeting: $meeting,
            title: 'Deuxième point',
        );

    expect($second->position)->toBe(2);
});

test('an agenda item cannot be added to an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    app(AddMeetingAgendaItemAction::class)
        ->execute(
            meeting: $meeting,
            title: 'Cotisations',
        );
})->throws(ValidationException::class);

test('removing an agenda item compacts positions', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $first = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Premier');

    $second = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Deuxième');

    $third = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Troisième');

    app(RemoveMeetingAgendaItemAction::class)
        ->execute($second);

    $items = $meeting
        ->agendaItems()
        ->get();

    expect($items)
        ->toHaveCount(2)
        ->and($items[0]->id)
        ->toBe($first->id)
        ->and($items[0]->position)
        ->toBe(1)
        ->and($items[1]->id)
        ->toBe($third->id)
        ->and($items[1]->position)
        ->toBe(2);
});

test('agenda items can be reordered', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $first = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Premier');

    $second = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Deuxième');

    $third = app(AddMeetingAgendaItemAction::class)
        ->execute($meeting, 'Troisième');

    app(ReorderMeetingAgendaItemsAction::class)
        ->execute(
            meeting: $meeting,
            itemIds: [
                $third->id,
                $first->id,
                $second->id,
            ],
        );

    $items = $meeting
        ->agendaItems()
        ->get();

    expect($items->pluck('id')->all())
        ->toBe([
            $third->id,
            $first->id,
            $second->id,
        ]);
});

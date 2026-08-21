<?php

use App\Actions\MeetingDecisions\AddMeetingDecisionAction;
use App\Actions\MeetingDecisions\DeleteMeetingDecisionAction;
use App\Actions\MeetingDecisions\UpdateMeetingDecisionAction;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingDecision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a decision can be added during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $creator = User::factory()->create();

    $decision = app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Maintien de la cotisation',
            description: 'Le montant reste à 40 000 FCFA.',
        );

    expect($decision)
        ->meeting_id->toBe($meeting->id)
        ->created_by->toBe($creator->id)
        ->title->toBe('Maintien de la cotisation')
        ->description->toBe(
            'Le montant reste à 40 000 FCFA.',
        );
});

test('a decision can be linked to an agenda item', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $agendaItem = MeetingAgendaItem::factory()
        ->for($meeting)
        ->create();

    $creator = User::factory()->create();

    $decision = app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Décision cotisations',
            agendaItem: $agendaItem,
        );

    expect($decision->meeting_agenda_item_id)
        ->toBe($agendaItem->id);
});

test('a decision can exist without an agenda item', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $creator = User::factory()->create();

    $decision = app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Décision générale',
        );

    expect($decision->meeting_agenda_item_id)
        ->toBeNull();
});

test('agenda item must belong to the same meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $otherMeeting = Meeting::factory()
        ->inProgress()
        ->create();

    $agendaItem = MeetingAgendaItem::factory()
        ->for($otherMeeting)
        ->create();

    $creator = User::factory()->create();

    app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Décision invalide',
            agendaItem: $agendaItem,
        );
})->throws(ValidationException::class);

test('a decision cannot be added before meeting opens', function (): void {
    $meeting = Meeting::factory()
        ->scheduled()
        ->create();

    $creator = User::factory()->create();

    app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Décision impossible',
        );
})->throws(ValidationException::class);

test('a decision cannot be added after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $creator = User::factory()->create();

    app(AddMeetingDecisionAction::class)
        ->execute(
            meeting: $meeting,
            creator: $creator,
            title: 'Décision impossible',
        );
})->throws(ValidationException::class);

test('a decision can be updated during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->create([
            'title' => 'Ancienne décision',
        ]);

    $decision = app(UpdateMeetingDecisionAction::class)
        ->execute(
            decision: $decision,
            title: 'Nouvelle décision',
            description: 'Nouvelle description',
        );

    expect($decision)
        ->title->toBe('Nouvelle décision')
        ->description->toBe(
            'Nouvelle description',
        );
});

test('a decision can change its agenda item', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $firstAgendaItem = MeetingAgendaItem::factory()
        ->for($meeting)
        ->create([
            'position' => 1,
        ]);

    $secondAgendaItem = MeetingAgendaItem::factory()
        ->for($meeting)
        ->create([
            'position' => 2,
        ]);

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->for(
            $firstAgendaItem,
            'agendaItem',
        )
        ->create();

    $decision = app(UpdateMeetingDecisionAction::class)
        ->execute(
            decision: $decision,
            title: $decision->title,
            description: $decision->description,
            agendaItem: $secondAgendaItem,
        );

    expect($decision->meeting_agenda_item_id)
        ->toBe($secondAgendaItem->id);
});

test('a decision can be detached from an agenda item', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $agendaItem = MeetingAgendaItem::factory()
        ->for($meeting)
        ->create();

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->for(
            $agendaItem,
            'agendaItem',
        )
        ->create();

    $decision = app(UpdateMeetingDecisionAction::class)
        ->execute(
            decision: $decision,
            title: $decision->title,
            description: $decision->description,
            agendaItem: null,
        );

    expect($decision->meeting_agenda_item_id)
        ->toBeNull();
});

test('a decision cannot be updated after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->create();

    app(UpdateMeetingDecisionAction::class)
        ->execute(
            decision: $decision,
            title: 'Modification interdite',
        );
})->throws(ValidationException::class);

test('a decision can be deleted during an in progress meeting', function (): void {
    $meeting = Meeting::factory()
        ->inProgress()
        ->create();

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->create();

    app(DeleteMeetingDecisionAction::class)
        ->execute($decision);

    expect(
        MeetingDecision::query()
            ->whereKey($decision->id)
            ->exists(),
    )->toBeFalse();
});

test('a decision cannot be deleted after meeting is completed', function (): void {
    $meeting = Meeting::factory()
        ->completed()
        ->create();

    $decision = MeetingDecision::factory()
        ->for($meeting)
        ->create();

    app(DeleteMeetingDecisionAction::class)
        ->execute($decision);
})->throws(ValidationException::class);

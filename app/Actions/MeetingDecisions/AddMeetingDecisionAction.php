<?php

namespace App\Actions\MeetingDecisions;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingDecision;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class AddMeetingDecisionAction
{
    public function execute(
        Meeting $meeting,
        User $creator,
        string $title,
        ?string $description = null,
        ?MeetingAgendaItem $agendaItem = null,
    ): MeetingDecision {
        if (
            $meeting->status
            !== MeetingStatus::InProgress
        ) {
            throw ValidationException::withMessages([
                'meeting' => __(
                    'Une décision ne peut être ajoutée que pendant une réunion en cours.'
                ),
            ]);
        }

        $this->ensureAgendaItemBelongsToMeeting(
            meeting: $meeting,
            agendaItem: $agendaItem,
        );

        $decision =
            new MeetingDecision();

        $decision->fill([
            'title' => $title,
            'description' => $description,
        ]);

        $decision->meeting()
            ->associate($meeting);

        $decision->creator()
            ->associate($creator);

        if ($agendaItem) {
            $decision->agendaItem()
                ->associate($agendaItem);
        }

        $decision->save();

        return $decision->refresh();
    }

    private function ensureAgendaItemBelongsToMeeting(
        Meeting $meeting,
        ?MeetingAgendaItem $agendaItem,
    ): void {
        if (
            $agendaItem
            && $agendaItem->meeting_id !== $meeting->id
        ) {
            throw ValidationException::withMessages([
                'meeting_agenda_item_id' => __(
                    'Ce point d’ordre du jour n’appartient pas à cette réunion.'
                ),
            ]);
        }
    }
}

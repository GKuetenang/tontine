<?php

namespace App\Actions\MeetingDecisions;

use App\Enums\MeetingStatus;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingDecision;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingDecisionAction
{
    public function execute(
        MeetingDecision $decision,
        string $title,
        ?string $description = null,
        ?MeetingAgendaItem $agendaItem = null,
    ): MeetingDecision {
        if (
            $decision->meeting->status
            !== MeetingStatus::InProgress
        ) {
            throw ValidationException::withMessages([
                'meeting' => __(
                    'Une décision ne peut être modifiée que pendant une réunion en cours.'
                ),
            ]);
        }

        if (
            $agendaItem
            && $agendaItem->meeting_id
            !== $decision->meeting_id
        ) {
            throw ValidationException::withMessages([
                'meeting_agenda_item_id' => __(
                    'Ce point d’ordre du jour n’appartient pas à cette réunion.'
                ),
            ]);
        }

        $decision->fill([
            'title' => $title,
            'description' => $description,
        ]);

        if ($agendaItem) {
            $decision->agendaItem()
                ->associate($agendaItem);
        } else {
            $decision->agendaItem()
                ->dissociate();
        }

        $decision->save();

        return $decision->refresh();
    }
}

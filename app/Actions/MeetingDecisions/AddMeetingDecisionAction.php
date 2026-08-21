<?php

namespace App\Actions\MeetingDecisions;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingDecision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(
            function () use (
                $meeting,
                $creator,
                $title,
                $description,
                $agendaItem,
            ): MeetingDecision {
                if (
                    $meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Les décisions ne peuvent être ajoutées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                if (
                    $agendaItem !== null
                    && $agendaItem->meeting_id
                    !== $meeting->id
                ) {
                    throw ValidationException::withMessages([
                        'agenda_item_id' => __(
                            'Ce point d’ordre du jour n’appartient pas à cette réunion.'
                        ),
                    ]);
                }

                $decision = new MeetingDecision();

                $decision->fill([
                    'title' => $title,
                    'description' => $description,
                ]);

                $decision->meeting()
                    ->associate($meeting);

                $decision->creator()
                    ->associate($creator);

                if ($agendaItem !== null) {
                    $decision->agendaItem()
                        ->associate($agendaItem);
                }

                $decision->save();

                return $decision->refresh();
            },
        );
    }
}

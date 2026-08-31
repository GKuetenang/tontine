<?php

namespace App\Actions\MeetingNotes;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddMeetingNoteAction
{
    public function execute(
        Meeting $meeting,
        User $creator,
        string $content,
        ?MeetingAgendaItem $agendaItem = null,
    ): MeetingNote {
        return DB::transaction(
            function () use (
                $meeting,
                $creator,
                $content,
                $agendaItem,
            ): MeetingNote {
                if (
                    $meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Les notes ne peuvent être ajoutées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                if (
                    $agendaItem !== null
                    && $agendaItem->meeting_id !== $meeting->id
                ) {
                    throw ValidationException::withMessages([
                        'meeting_agenda_item_id' => __(
                            'Ce point d’ordre du jour n’appartient pas à cette réunion.'
                        ),
                    ]);
                }

                $note = new MeetingNote;

                $note->fill([
                    'content' => $content,
                ]);

                $note->meeting()
                    ->associate($meeting);

                $note->creator()
                    ->associate($creator);

                if ($agendaItem !== null) {
                    $note->agendaItem()
                        ->associate($agendaItem);
                }

                $note->save();

                return $note->refresh();
            },
        );
    }
}

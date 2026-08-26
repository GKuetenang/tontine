<?php

namespace App\Actions\MeetingNotes;

use App\Enums\MeetingStatus;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingNoteAction
{
    public function execute(
        MeetingNote $note,
        string $content,
        ?MeetingAgendaItem $agendaItem = null,
    ): MeetingNote {
        return DB::transaction(
            function () use (
                $note,
                $content,
                $agendaItem,
            ): MeetingNote {
                if (
                    $note->meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Les notes ne peuvent être modifiées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                if (
                    $agendaItem !== null
                    && $agendaItem->meeting_id !== $note->meeting_id
                ) {
                    throw ValidationException::withMessages([
                        'meeting_agenda_item_id' => __(
                            'Ce point d’ordre du jour n’appartient pas à cette réunion.'
                        ),
                    ]);
                }

                $note->fill([
                    'content' => $content,
                ]);

                if ($agendaItem !== null) {
                    $note->agendaItem()
                        ->associate($agendaItem);
                } else {
                    $note->agendaItem()
                        ->dissociate();
                }

                $note->save();

                return $note->refresh();
            },
        );
    }
}

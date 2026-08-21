<?php

namespace App\Actions\MeetingNotes;

use App\Enums\MeetingStatus;
use App\Models\MeetingNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingNoteAction
{
    public function execute(
        MeetingNote $note,
        string $content,
    ): MeetingNote {
        return DB::transaction(
            function () use (
                $note,
                $content,
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

                $note->update([
                    'content' => $content,
                ]);

                return $note->refresh();
            },
        );
    }
}

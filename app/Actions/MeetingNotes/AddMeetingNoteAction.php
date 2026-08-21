<?php

namespace App\Actions\MeetingNotes;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
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
    ): MeetingNote {
        return DB::transaction(
            function () use (
                $meeting,
                $creator,
                $content,
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

                $note = new MeetingNote();

                $note->fill([
                    'content' => $content,
                ]);

                $note->meeting()
                    ->associate($meeting);

                $note->creator()
                    ->associate($creator);

                $note->save();

                return $note->refresh();
            },
        );
    }
}

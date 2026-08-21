<?php

namespace App\Actions\MeetingNotes;

use App\Enums\MeetingStatus;
use App\Models\MeetingNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteMeetingNoteAction
{
    public function execute(
        MeetingNote $note,
    ): void {
        DB::transaction(
            function () use ($note): void {
                if (
                    $note->meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Les notes ne peuvent être supprimées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                $note->delete();
            },
        );
    }
}

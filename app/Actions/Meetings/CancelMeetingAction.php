<?php

namespace App\Actions\Meetings;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CancelMeetingAction
{
    public function execute(Meeting $meeting): Meeting
    {
        return DB::transaction(function () use ($meeting): Meeting {
            if (
                $meeting->status
                !== MeetingStatus::Scheduled
            ) {
                throw ValidationException::withMessages([
                    'meeting' => __(
                        'Seule une assise prévue peut être annulée.'
                    ),
                ]);
            }

            $meeting->forceFill([
                'status' => MeetingStatus::Cancelled,
            ])->save();

            return $meeting->refresh();
        });
    }
}

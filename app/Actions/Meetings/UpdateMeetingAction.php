<?php

namespace App\Actions\Meetings;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingAction
{
    public function execute(
        Meeting $meeting,
        string $title,
        CarbonImmutable $scheduledAt,
        ?string $description = null,
        ?string $location = null,
    ): Meeting {
        return DB::transaction(
            function () use (
                $meeting,
                $title,
                $scheduledAt,
                $description,
                $location,
            ): Meeting {
                if (
                    $meeting->status
                    !== MeetingStatus::Scheduled
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Seule une assise prévue peut être modifiée.'
                        ),
                    ]);
                }

                $meeting->update([
                    'title' => $title,
                    'description' => $description,
                    'scheduled_at' => $scheduledAt,
                    'location' => $location,
                ]);

                return $meeting->refresh();
            },
        );
    }
}

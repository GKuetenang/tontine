<?php

namespace App\Actions\MeetingAttendances;

use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BulkUpdateMeetingAttendancesAction
{
    /**
     * @param array<int, array{
     *     id: int,
     *     status: AttendanceStatus,
     *     note?: string|null
     * }> $items
     */
    public function execute(
        Meeting $meeting,
        array $items,
    ): Meeting {
        return DB::transaction(
            function () use (
                $meeting,
                $items,
            ): Meeting {
                if (
                    $meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'attendances' => __(
                            'Les présences ne peuvent être modifiées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                $attendanceIds = $meeting
                    ->attendances()
                    ->pluck('id')
                    ->all();

                foreach ($items as $item) {
                    if (
                        ! in_array(
                            $item['id'],
                            $attendanceIds,
                            true,
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'attendances' => __(
                                'Une présence ne correspond pas à cette réunion.'
                            ),
                        ]);
                    }

                    $status = $item['status'];

                    $checkedInAt = match ($status) {
                        AttendanceStatus::Present,
                        AttendanceStatus::Late => now(),

                        default => null,
                    };

                    $meeting
                        ->attendances()
                        ->whereKey($item['id'])
                        ->update([
                            'status' => $status,
                            'checked_in_at' => $checkedInAt,
                            'note' => $item['note'] ?? null,
                        ]);
                }

                return $meeting
                    ->refresh()
                    ->load([
                        'attendances.sessionParticipant.membership.user',
                    ]);
            },
        );
    }
}

<?php

namespace App\Actions\MeetingAttendances;

use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Models\MeetingAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingAttendanceAction
{
    public function execute(
        MeetingAttendance $attendance,
        AttendanceStatus $status,
        ?string $note = null,
    ): MeetingAttendance {
        return DB::transaction(
            function () use (
                $attendance,
                $status,
                $note,
            ): MeetingAttendance {
                if (
                    $attendance->meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'attendance' => __(
                            'Les présences ne peuvent être modifiées que pendant une assise en cours.'
                        ),
                    ]);
                }

                $checkedInAt = match ($status) {
                    AttendanceStatus::Present,
                    AttendanceStatus::Late => now(),

                    default => null,
                };

                $attendance->update([
                    'status' => $status,
                    'checked_in_at' => $checkedInAt,
                    'note' => $note,
                ]);

                return $attendance->refresh();
            },
        );
    }
}

<?php

namespace App\Actions\Meetings;

use App\Actions\Penalties\ApplyAutomaticMeetingPenaltiesAction;
use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CloseMeetingAction
{
    public function __construct(
        private readonly ApplyAutomaticMeetingPenaltiesAction $applyPenalties,
    ) {}

    public function execute(Meeting $meeting): Meeting
    {
        return DB::transaction(function () use ($meeting): Meeting {
            if (
                $meeting->status
                !== MeetingStatus::InProgress
            ) {
                throw ValidationException::withMessages([
                    'meeting' => __(
                        'Seule une assise en cours peut être clôturée.'
                    ),
                ]);
            }

            $meeting
                ->attendances()
                ->where(
                    'status',
                    AttendanceStatus::Pending,
                )
                ->update([
                    'status' => AttendanceStatus::Absent,
                    'checked_in_at' => null,
                    'updated_at' => now(),
                ]);

            $meeting->unsetRelation('attendances');

            $closedAt = now()->toImmutable();

            $this->applyPenalties->execute($meeting, $closedAt);

            $meeting->forceFill([
                'status' => MeetingStatus::Completed,
                'closed_at' => $closedAt,
            ])->save();

            return $meeting
                ->refresh()
                ->load([
                    'attendances.sessionParticipant.membership.user',
                    'contributions',
                    'notes',
                    'decisions',
                ]);
        });
    }
}

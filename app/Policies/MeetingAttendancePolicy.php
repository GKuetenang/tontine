<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class MeetingAttendancePolicy
{
    use ChecksGroupPermissions;

    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewMeetingAttendances,
            );
    }

    public function view(
        User $user,
        MeetingAttendance $attendance,
    ): bool {
        $group = $attendance
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewMeetingAttendances,
            );
    }

    public function update(
        User $user,
        MeetingAttendance $attendance,
    ): bool {
        $group = $attendance
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::UpdateMeetingAttendances,
            );
    }
}

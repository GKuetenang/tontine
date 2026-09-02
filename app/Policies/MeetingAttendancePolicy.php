<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\User;

class MeetingAttendancePolicy
{
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

    private function can(
        User $user,
        Group $group,
        GroupPermission $permission,
    ): bool {
        $previousTeamId =
            getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $group->id,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation(
                'permissions',
            );

            return $user->can(
                $permission->value,
            );
        } finally {
            setPermissionsTeamId(
                $previousTeamId,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation(
                'permissions',
            );
        }
    }
}

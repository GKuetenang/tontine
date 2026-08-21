<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Tontine;
use App\Models\User;

class MeetingAttendancePolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewMeetingAttendances,
            );
    }

    public function view(
        User $user,
        MeetingAttendance $attendance,
    ): bool {
        $tontine = $attendance
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewMeetingAttendances,
            );
    }

    public function update(
        User $user,
        MeetingAttendance $attendance,
    ): bool {
        $tontine = $attendance
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateMeetingAttendances,
            );
    }

    private function can(
        User $user,
        Tontine $tontine,
        TontinePermission $permission,
    ): bool {
        $previousTeamId =
            getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $tontine->id,
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

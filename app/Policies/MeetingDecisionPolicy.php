<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\MeetingDecision;
use App\Models\Tontine;
use App\Models\User;

class MeetingDecisionPolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewMeetingDecisions,
            );
    }

    public function view(
        User $user,
        MeetingDecision $decision,
    ): bool {
        return $this->viewAny(
            $user,
            $decision->meeting,
        );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::CreateMeetingDecisions,
            );
    }

    public function update(
        User $user,
        MeetingDecision $decision,
    ): bool {
        $tontine = $decision
            ->meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateMeetingDecisions,
            );
    }

    public function delete(
        User $user,
        MeetingDecision $decision,
    ): bool {
        $tontine = $decision
            ->meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::DeleteMeetingDecisions,
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
            $user->unsetRelation('permissions');

            return $user->can(
                $permission->value,
            );
        } finally {
            setPermissionsTeamId(
                $previousTeamId,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

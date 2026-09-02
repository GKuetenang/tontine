<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingDecision;
use App\Models\User;

class MeetingDecisionPolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewMeetingDecisions,
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
        $group = $meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::CreateMeetingDecisions,
            );
    }

    public function update(
        User $user,
        MeetingDecision $decision,
    ): bool {
        $group = $decision
            ->meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::UpdateMeetingDecisions,
            );
    }

    public function delete(
        User $user,
        MeetingDecision $decision,
    ): bool {
        $group = $decision
            ->meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::DeleteMeetingDecisions,
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

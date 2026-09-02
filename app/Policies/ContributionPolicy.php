<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Contribution;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\User;

class ContributionPolicy
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
                $user,
                $group,
                GroupPermission::ViewContributions,
            );
    }

    public function view(
        User $user,
        Contribution $contribution,
    ): bool {
        $group = $contribution
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::ViewContributions,
            );
    }

    public function pay(
        User $user,
        Contribution $contribution,
    ): bool {
        $group = $contribution
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::RecordContributionPayments,
            );
    }

    private function can(
        User $user,
        Group $group,
        GroupPermission $permission,
    ): bool {
        $previousTeamId = getPermissionsTeamId();

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

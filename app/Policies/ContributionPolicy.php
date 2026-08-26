<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\Tontine;
use App\Models\User;

class ContributionPolicy
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
                $user,
                $tontine,
                TontinePermission::ViewContributions,
            );
    }

    public function view(
        User $user,
        Contribution $contribution,
    ): bool {
        $tontine = $contribution
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::ViewContributions,
            );
    }

    public function pay(
        User $user,
        Contribution $contribution,
    ): bool {
        $tontine = $contribution
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::RecordContributionPayments,
            );
    }

    private function can(
        User $user,
        Tontine $tontine,
        TontinePermission $permission,
    ): bool {
        $previousTeamId = getPermissionsTeamId();

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

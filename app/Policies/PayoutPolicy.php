<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\Tontine;
use App\Models\User;

class PayoutPolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine =
            $meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership(
                $user,
            )
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewPayouts,
            );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine =
            $meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership(
                $user,
            )
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::CreatePayouts,
            );
    }

    public function update(
        User $user,
        Payout $payout,
    ): bool {
        $tontine =
            $payout
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership(
                $user,
            )
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdatePayouts,
            );
    }

    public function pay(
        User $user,
        Payout $payout,
    ): bool {
        $tontine =
            $payout
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership(
                $user,
            )
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::PayPayouts,
            );
    }

    public function cancel(
        User $user,
        Payout $payout,
    ): bool {
        $tontine =
            $payout
            ->meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership(
                $user,
            )
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::CancelPayouts,
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

<?php

namespace App\Support;

use App\Data\TontineAbilitiesData;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class TontineAbilities
{
    public function for(
        User $user,
        Tontine $tontine,
    ): TontineAbilitiesData {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return new TontineAbilitiesData(
                view: Gate::forUser($user)->allows(
                    'view',
                    $tontine,
                ),

                update: Gate::forUser($user)->allows(
                    'update',
                    $tontine,
                ),

                delete: Gate::forUser($user)->allows(
                    'delete',
                    $tontine,
                ),
                view_memberships: Gate::forUser($user)->allows(
                    'viewAny',
                    [Membership::class, $tontine]
                )
            );
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

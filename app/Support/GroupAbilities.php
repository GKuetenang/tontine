<?php

namespace App\Support;

use App\Data\GroupAbilitiesData;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class GroupAbilities
{
    public function for(
        User $user,
        Group $group,
    ): GroupAbilitiesData {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return new GroupAbilitiesData(
                view: Gate::forUser($user)->allows(
                    'view',
                    $group,
                ),

                update: Gate::forUser($user)->allows(
                    'update',
                    $group,
                ),

                delete: Gate::forUser($user)->allows(
                    'delete',
                    $group,
                ),
                view_memberships: Gate::forUser($user)->allows(
                    'viewAny',
                    [Membership::class, $group]
                )
            );
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

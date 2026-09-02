<?php

namespace App\Actions\Memberships;

use App\Models\Membership;
use Illuminate\Support\Facades\DB;

final class ChangeMembershipRoleAction
{
    public function execute(
        Membership $membership,
        string $roleName,
    ): Membership {
        return DB::transaction(function () use (
            $membership,
            $roleName,
        ): Membership {
            $user = $membership->user;

            $previousTeamId = getPermissionsTeamId();

            try {
                setPermissionsTeamId(
                    $membership->group_id,
                );

                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');

                $user->syncRoles([$roleName]);
            } finally {
                setPermissionsTeamId($previousTeamId);

                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
            }

            return $membership->refresh();
        });
    }
}

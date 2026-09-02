<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;

class UpdateMembershipAction
{
    public function execute(
        Membership $membership,
        Group $group,
        string $roleName,
        MembershipStatus $status = MembershipStatus::Active,
    ): Membership {
        if ($membership->trashed()) {
            $membership->restore();
        }

        $membership->load('user');
        $user = $membership->user;

        $membership->fill([
            'status' => $status,
            'left_at' => null,
        ]);

        $membership->save();

        $this->syncRole(
            group: $group,
            user: $user,
            roleName: $roleName,
        );

        return $membership->fresh();
    }

    private function syncRole(
        Group $group,
        User $user,
        string $roleName,
    ): void {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->syncRoles([$roleName]);
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;

class UpdateMembershipAction
{
    public function execute(
        Membership $membership,
        Tontine $tontine,
        User $user,
        string $roleName,
        ?User $invitedBy = null,
        MembershipStatus $status = MembershipStatus::Active,
    ): Membership {
        if ($membership->trashed()) {
            $membership->restore();
        }

        $membership->user()->associate($user);
        $membership->tontine()->associate($tontine);

        if ($invitedBy !== null) {
            $membership->inviter()->associate($invitedBy);
        } else {
            $membership->inviter()->dissociate();
        }

        $membership->fill([
            'status' => $status,
            'left_at' => null
        ]);

        $membership->save();

        $this->syncRole(
            tontine: $tontine,
            user: $user,
            roleName: $roleName,
        );

        return $membership->fresh();
    }

    private function syncRole(
        Tontine $tontine,
        User $user,
        string $roleName,
    ): void {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

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

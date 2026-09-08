<?php

namespace App\Actions\Memberships;

use App\Actions\Mandates\SyncMemberMandateRolesAction;
use App\Enums\GroupRole;
use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;

class UpdateMembershipAction
{
    public function __construct(private SyncMemberMandateRolesAction $syncMandateRoles) {}

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
            roleName: $group->mandates()->where('status', '!=', 'draft')->exists()
                ? GroupRole::Member->value
                : $roleName,
        );

        $this->syncMandateRoles->execute($group, $user);

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

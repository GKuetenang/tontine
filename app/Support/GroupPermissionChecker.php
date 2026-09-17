<?php

namespace App\Support;

use App\Actions\Mandates\SyncMemberMandateRolesAction;
use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\User;

final class GroupPermissionChecker
{
    public function __construct(private SyncMemberMandateRolesAction $syncMemberRoles) {}

    public function allows(User $user, Group $group, GroupPermission|string $permission): bool
    {
        $previousTeamId = getPermissionsTeamId();

        try {
            $this->prepare($user, $group);

            return $group->hasActiveMembership($user)
                && $user->can($permission instanceof GroupPermission ? $permission->value : $permission);
        } finally {
            setPermissionsTeamId($previousTeamId);
            $this->forgetLoadedPermissions($user);
        }
    }

    public function prepare(User $user, Group $group): void
    {
        setPermissionsTeamId($group->id);
        $this->forgetLoadedPermissions($user);
        $this->syncMemberRoles->execute($group, $user);
        $this->forgetLoadedPermissions($user);
    }

    private function forgetLoadedPermissions(User $user): void
    {
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');
    }
}

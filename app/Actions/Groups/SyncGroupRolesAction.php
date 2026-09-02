<?php

namespace App\Actions\Groups;

use App\Enums\GroupPermission;
use App\Enums\GroupRole;
use App\Models\Group;
use LogicException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SyncGroupRolesAction
{
    public function execute(Group $group): void
    {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);

            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();

            foreach (GroupRole::cases() as $roleName) {
                $role = Role::query()
                    ->firstOrCreate([
                        'name' => $roleName->value,
                        'guard_name' => 'web',
                        'group_id' => $group->id,
                    ]);

                $permissionNames = array_map(
                    static fn (
                        GroupPermission $permission,
                    ): string => $permission->value,
                    $roleName->defaultPermissions(),
                );

                $permissions = Permission::query()
                    ->where('guard_name', 'web')
                    ->whereIn('name', $permissionNames)
                    ->get();

                $missingPermissions = array_diff(
                    $permissionNames,
                    $permissions->pluck('name')->all(),
                );

                if ($missingPermissions !== []) {
                    throw new LogicException(
                        'Permissions manquantes : '
                            .implode(', ', $missingPermissions)
                    );
                }

                $role->syncPermissions($permissions);
            }
        } finally {
            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();

            setPermissionsTeamId($previousTeamId);
        }
    }
}

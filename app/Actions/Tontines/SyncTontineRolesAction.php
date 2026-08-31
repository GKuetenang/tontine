<?php

namespace App\Actions\Tontines;

use App\Enums\TontinePermission;
use App\Enums\TontineRole;
use App\Models\Tontine;
use LogicException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SyncTontineRolesAction
{
    public function execute(Tontine $tontine): void
    {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();

            foreach (TontineRole::cases() as $roleName) {
                $role = Role::query()
                    ->firstOrCreate([
                        'name' => $roleName->value,
                        'guard_name' => 'web',
                        'tontine_id' => $tontine->id,
                    ]);

                $permissionNames = array_map(
                    static fn (
                        TontinePermission $permission,
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

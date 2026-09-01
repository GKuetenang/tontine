<?php

namespace App\Actions\Roles;

use App\Models\Tontine;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SaveTontineRoleAction
{
    public function execute(
        Tontine $tontine,
        string $name,
        array $permissionNames,
        ?Role $role = null,
    ): Role {
        return DB::transaction(function () use ($tontine, $name, $permissionNames, $role): Role {
            $role ??= new Role;
            $role->forceFill([
                'name' => $name,
                'guard_name' => 'web',
                'tontine_id' => $tontine->id,
            ])->save();

            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', $permissionNames)
                ->get();

            $role->syncPermissions($permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $role->refresh();
        });
    }
}

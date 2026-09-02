<?php

namespace App\Actions\Roles;

use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class SaveGroupRoleAction
{
    public function execute(
        Group $group,
        string $name,
        array $permissionNames,
        ?Role $role = null,
    ): Role {
        return DB::transaction(function () use ($group, $name, $permissionNames, $role): Role {
            $role ??= new Role;
            $role->forceFill([
                'name' => $name,
                'guard_name' => 'web',
                'group_id' => $group->id,
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

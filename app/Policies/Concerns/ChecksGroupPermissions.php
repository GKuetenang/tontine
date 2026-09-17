<?php

namespace App\Policies\Concerns;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;
use App\Support\GroupPermissionChecker;

trait ChecksGroupPermissions
{
    private function can(
        User $user,
        Group|Session $group,
        GroupPermission $permission,
    ): bool {
        $ctx = $group instanceof Session ? $group->group : $group;

        return app(GroupPermissionChecker::class)->allows(
            user: $user,
            group: $ctx,
            permission: $permission,
        );
    }
}

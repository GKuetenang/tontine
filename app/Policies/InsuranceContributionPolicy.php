<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;

class InsuranceContributionPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session->group, GroupPermission::ViewInsurance);
    }

    public function create(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session->group, GroupPermission::ManageInsurance);
    }

    private function can(User $user, Group $group, GroupPermission $permission): bool
    {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return $user->can($permission->value);
        } finally {
            setPermissionsTeamId($previousTeamId);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

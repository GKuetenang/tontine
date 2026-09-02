<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Loan;
use App\Models\Session;
use App\Models\User;

class RepaymentPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session, GroupPermission::ViewRepayments);
    }

    public function create(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session, GroupPermission::CreateRepayments);
    }

    private function can(User $user, Session $session, GroupPermission $permission): bool
    {
        $group = $session->group;
        if (! $group->hasActiveMembership($user)) {
            return false;
        }
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

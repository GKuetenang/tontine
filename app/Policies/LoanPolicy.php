<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Session;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::ViewLoans);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::CreateLoans);
    }

    public function approve(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->group, GroupPermission::ApproveLoans);
    }

    public function cancel(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->group, GroupPermission::DeleteLoans);
    }

    private function can(User $user, Group $group, GroupPermission $permission): bool
    {
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

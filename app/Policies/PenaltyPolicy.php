<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Penalty;
use App\Models\Session;
use App\Models\User;

class PenaltyPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session, GroupPermission::ViewPenalties);
    }

    public function create(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session, GroupPermission::CreatePenalties);
    }

    public function update(User $user, Penalty $penalty): bool
    {
        return $penalty->meeting->session->group->hasActiveMembership($user)
            && $this->can($user, $penalty->meeting->session, GroupPermission::UpdatePenalties);
    }

    private function can(User $user, Session $session, GroupPermission $permission): bool
    {
        $previousTeamId = getPermissionsTeamId();
        try {
            setPermissionsTeamId($session->group_id);
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

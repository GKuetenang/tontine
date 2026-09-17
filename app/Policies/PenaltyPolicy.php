<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Penalty;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class PenaltyPolicy
{
    use ChecksGroupPermissions;

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
}

<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class InsuranceContributionPolicy
{
    use ChecksGroupPermissions;

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
}

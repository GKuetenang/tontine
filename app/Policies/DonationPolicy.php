<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Donation;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::ViewDonations);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::CreateDonations);
    }

    public function pay(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->group, GroupPermission::PayDonations);
    }

    public function cancel(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->group, GroupPermission::CancelDonations);
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

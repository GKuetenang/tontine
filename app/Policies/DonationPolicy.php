<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Donation;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->tontine, TontinePermission::ViewDonations);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->tontine, TontinePermission::CreateDonations);
    }

    public function pay(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->tontine, TontinePermission::PayDonations);
    }

    public function cancel(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->tontine, TontinePermission::CancelDonations);
    }

    private function can(User $user, Tontine $tontine, TontinePermission $permission): bool
    {
        if (! $tontine->hasActiveMembership($user)) {
            return false;
        }

        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);
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

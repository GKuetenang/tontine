<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $user, Tontine $tontine): bool
    {
        return $this->canManageTontine($user, $tontine);
    }

    public function view(User $user, Membership $membership): bool
    {
        return $this->canManageTontine($user, $membership->tontine);
    }

    public function create(User $user, Tontine $tontine): bool
    {
        return $this->canManageTontine($user, $tontine);
    }

    public function update(User $user, Membership $membership): bool
    {
        return $this->canManageTontine($user, $membership->tontine);
    }

    public function delete(User $user, Membership $membership): bool
    {
        return $this->canManageTontine($user, $membership->tontine)
            && ! $membership->isLastPresident();
    }

    public function restore(User $user, Membership $membership): bool
    {
        return $this->canManageTontine($user, $membership->tontine);
    }

    public function forceDelete(User $user, Membership $membership): bool
    {
        return $this->canManageTontine($user, $membership->tontine)
            && ! $membership->isLastPresident();
    }

    private function canManageTontine(User $user, Tontine $tontine): bool
    {
        return $user->id === $tontine->user_id || $user->hasRole('president');
    }
}

<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Tontine;
use App\Models\User;

class TontinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::ViewTontine->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::UpdateTontine->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::DeleteTontine->value);;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::RestoreTontine->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::ForceDeleteTontine->value);
    }
}

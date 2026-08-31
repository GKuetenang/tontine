<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class InsuranceContributionPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $session->tontine->hasActiveMembership($user)
            && $this->can($user, $session->tontine, TontinePermission::ViewInsurance);
    }

    public function create(User $user, Session $session): bool
    {
        return $session->tontine->hasActiveMembership($user)
            && $this->can($user, $session->tontine, TontinePermission::ManageInsurance);
    }

    private function can(User $user, Tontine $tontine, TontinePermission $permission): bool
    {
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

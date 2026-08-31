<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Loan;
use App\Models\Session;
use App\Models\User;

class RepaymentPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session, TontinePermission::ViewRepayments);
    }

    public function create(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session, TontinePermission::CreateRepayments);
    }

    private function can(User $user, Session $session, TontinePermission $permission): bool
    {
        $tontine = $session->tontine;
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

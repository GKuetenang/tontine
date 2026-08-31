<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Loan;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->tontine, TontinePermission::ViewLoans);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->tontine, TontinePermission::CreateLoans);
    }

    public function approve(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->tontine, TontinePermission::ApproveLoans);
    }

    public function cancel(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->tontine, TontinePermission::DeleteLoans);
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

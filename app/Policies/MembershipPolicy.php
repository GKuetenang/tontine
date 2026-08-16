<?php

namespace App\Policies;

use App\Enums\MembershipStatus;
use App\Enums\TontinePermission;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;

class MembershipPolicy
{
    /**
     * Consulter la liste des membres d’une tontine.
     */
    public function viewAny(
        User $user,
        Tontine $tontine,
    ): bool {
        return $tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::ViewMemberships,
            );
    }

    /**
     * Consulter un membre précis.
     */
    public function view(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->tontine->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->tontine,
            TontinePermission::ViewMemberships,
        );
    }

    /**
     * Ajouter un membre à une tontine.
     */
    public function create(
        User $user,
        Tontine $tontine,
    ): bool {
        return $tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::CreateMemberships,
            );
    }

    /**
     * Modifier un membership.
     */
    public function update(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->tontine->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->tontine,
            TontinePermission::UpdateMemberships,
        );
    }

    /**
     * Retirer un membre de la tontine.
     */
    public function delete(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->tontine->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->tontine,
            TontinePermission::DeleteMemberships,
        );
    }

    /**
     * Vérifie une permission dans le contexte exact
     * de la tontine concernée.
     */
    private function can(
        User $user,
        Tontine $tontine,
        TontinePermission $permission,
    ): bool {
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

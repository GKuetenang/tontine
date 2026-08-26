<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Draw;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class DrawPolicy
{
    /**
     * Consulter le tirage d'une session.
     */
    public function view(
        User $user,
        Draw $draw,
    ): bool {
        return $draw->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $draw->session->tontine,
                TontinePermission::ViewDraws,
            );
    }

    /**
     * Générer le tirage d'une session.
     */
    public function generate(
        User $user,
        Session $session,
    ): bool {
        return $session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->tontine,
                TontinePermission::GenerateDraws,
            );
    }

    /**
     * Confirmer un tirage.
     */
    public function confirm(
        User $user,
        Draw $draw,
    ): bool {
        return $draw->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $draw->session->tontine,
                TontinePermission::ConfirmDraws,
            );
    }

    /**
     * Réinitialiser un tirage.
     */
    public function reset(
        User $user,
        Draw $draw,
    ): bool {
        return $draw->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $draw->session->tontine,
                TontinePermission::ResetDraws,
            );
    }

    /**
     * Supprimer un tirage.
     */
    public function delete(
        User $user,
        Draw $draw,
    ): bool {
        return $draw->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $draw->session->tontine,
                TontinePermission::DeleteDraws,
            );
    }

    public function update(
        User $user,
        Draw $draw,
    ): bool {
        $tontine = $draw
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateDraws,
            );
    }

    public function restore(
        User $user,
        Draw $draw,
    ): bool {
        return $draw->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $draw->session->tontine,
                TontinePermission::RestoreDraws,
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

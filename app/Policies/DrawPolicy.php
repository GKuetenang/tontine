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
     * Consulter le module de tirage d'une session.
     */
    public function view(
        User $user,
        Session $session,
    ): bool {
        $tontine =
            $session->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewDraws,
            );
    }

    /**
     * Générer un tirage.
     */
    public function generate(
        User $user,
        Session $session,
    ): bool {
        $tontine =
            $session->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::GenerateDraws,
            );
    }

    /**
     * Modifier un tirage existant.
     *
     * Utilisé notamment pour le swap.
     */
    public function update(
        User $user,
        Draw $draw,
    ): bool {
        $tontine =
            $draw
                ->session
                ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateDraws,
            );
    }

    /**
     * Réinitialiser un tirage.
     */
    public function reset(
        User $user,
        Draw $draw,
    ): bool {
        $tontine =
            $draw
                ->session
                ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ResetDraws,
            );
    }

    /**
     * Confirmer un tirage.
     */
    public function confirm(
        User $user,
        Draw $draw,
    ): bool {
        $tontine =
            $draw
                ->session
                ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ConfirmDraws,
            );
    }

    /**
     * Supprimer un tirage.
     */
    public function delete(
        User $user,
        Draw $draw,
    ): bool {
        $tontine =
            $draw
                ->session
                ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::DeleteDraws,
            );
    }

    private function can(
        User $user,
        Tontine $tontine,
        TontinePermission $permission,
    ): bool {
        $previousTeamId =
            getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $tontine->id,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return $user->can(
                $permission->value,
            );
        } finally {
            setPermissionsTeamId(
                $previousTeamId,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

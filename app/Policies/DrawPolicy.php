<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Draw;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class DrawPolicy
{
    use ChecksGroupPermissions;

    /**
     * Consulter le module de tirage d'une session.
     */
    public function view(
        User $user,
        Session $session,
    ): bool {
        $group =
            $session->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewDraws,
            );
    }

    /**
     * Générer un tirage.
     */
    public function generate(
        User $user,
        Session $session,
    ): bool {
        $group =
            $session->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::GenerateDraws,
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
        $group =
            $draw
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::UpdateDraws,
            );
    }

    /**
     * Réinitialiser un tirage.
     */
    public function reset(
        User $user,
        Draw $draw,
    ): bool {
        $group =
            $draw
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ResetDraws,
            );
    }

    /**
     * Confirmer un tirage.
     */
    public function confirm(
        User $user,
        Draw $draw,
    ): bool {
        $group =
            $draw
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ConfirmDraws,
            );
    }

    /**
     * Supprimer un tirage.
     */
    public function delete(
        User $user,
        Draw $draw,
    ): bool {
        $group =
            $draw
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::DeleteDraws,
            );
    }
}

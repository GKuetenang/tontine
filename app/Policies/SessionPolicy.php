<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    /**
     * Consulter la liste des membres d’une réunion.
     */
    public function viewAny(
        User $user,
        Group $group,
    ): bool {
        return $group->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::ViewSessions,
            );
    }

    /**
     * Consulter un membre précis.
     */
    public function view(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $session->group,
            GroupPermission::ViewSessions,
        );
    }

    /**
     * Ajouter un membre à une réunion.
     */
    public function create(
        User $user,
        Group $group,
    ): bool {
        return $group->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::CreateSessions,
            );
    }

    /**
     * Modifier un membership.
     */
    public function update(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $session->group,
            GroupPermission::UpdateSessions,
        );
    }

    /**
     * Retirer un membre de la réunion.
     */
    public function delete(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $session->group,
            GroupPermission::DeleteSessions,
        );
    }

    /**
     * Retirer un membre de la réunion.
     */
    public function activate(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $session->group,
            GroupPermission::ActivateSessions,
        );
    }

    /**
     * Retirer un membre de la réunion.
     */
    public function close(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $session->group,
            GroupPermission::CloseSessions,
        );
    }

    /**
     * Vérifie une permission dans le contexte exact
     * de la réunion concernée.
     */
    private function can(
        User $user,
        Group $group,
        GroupPermission $permission,
    ): bool {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);

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

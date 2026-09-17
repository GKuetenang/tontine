<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class SessionPolicy
{
    use ChecksGroupPermissions;

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

    public function restore(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session->group, GroupPermission::RestoreSessions);
    }

    public function forceDelete(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session->group, GroupPermission::ForceDeleteSessions);
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

    public function prepare(User $user, Session $session): bool
    {
        return $session->group->hasActiveMembership($user)
            && $this->can($user, $session->group, GroupPermission::PrepareSessions);
    }

    /**
     * Vérifie une permission dans le contexte exact
     * de la réunion concernée.
     */
}

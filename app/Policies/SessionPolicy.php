<?php

namespace App\Policies;

use App\Enums\MembershipStatus;
use App\Enums\TontinePermission;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class SessionPolicy
{
    /**
     * Consulter la liste des membres d’une tontine.
     */
    public function viewAny(
        User $user,
        Tontine $tontine,
    ): bool {
        return $this->isActiveMember($user, $tontine)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::ViewSessions,
            );
    }

    /**
     * Consulter un membre précis.
     */
    public function view(
        User $user,
        Session $session,
    ): bool {
        return $this->isActiveMember(
            $user,
            $session->tontine,
        ) && $this->can(
            $user,
            $session->tontine,
            TontinePermission::ViewSessions,
        );
    }

    /**
     * Ajouter un membre à une tontine.
     */
    public function create(
        User $user,
        Tontine $tontine,
    ): bool {
        return $this->isActiveMember($user, $tontine)
            && $this->can(
                $user,
                $tontine,
                TontinePermission::CreateSessions,
            );
    }

    /**
     * Modifier un membership.
     */
    public function update(
        User $user,
        Session $session,
    ): bool {
        return $this->isActiveMember(
            $user,
            $session->tontine,
        ) && $this->can(
            $user,
            $session->tontine,
            TontinePermission::UpdateSessions,
        );
    }

    /**
     * Retirer un membre de la tontine.
     */
    public function delete(
        User $user,
        Session $session,
    ): bool {
        return $this->isActiveMember(
            $user,
            $session->tontine,
        ) && $this->can(
            $user,
            $session->tontine,
            TontinePermission::DeleteSessions,
        );
    }

    /**
     * Retirer un membre de la tontine.
     */
    public function activate(
        User $user,
        Session $session,
    ): bool {
        return $this->isActiveMember(
            $user,
            $session->tontine,
        ) && $this->can(
            $user,
            $session->tontine,
            TontinePermission::ActivateSessions,
        );
    }

    /**
     * Retirer un membre de la tontine.
     */
    public function close(
        User $user,
        Session $session,
    ): bool {
        return $this->isActiveMember(
            $user,
            $session->tontine,
        ) && $this->can(
            $user,
            $session->tontine,
            TontinePermission::CloseSessions,
        );
    }

    /**
     * Vérifie que l’utilisateur connecté possède
     * un membership actif dans cette tontine.
     */
    private function isActiveMember(
        User $user,
        Tontine $tontine,
    ): bool {
        return $tontine
            ->memberships()
            ->where('user_id', $user->id)
            ->where('status', MembershipStatus::Active)
            ->exists();
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

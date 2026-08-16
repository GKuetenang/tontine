<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use App\Models\User;

class SessionParticipantPolicy
{
    /**
     * Consulter les participants d'une session.
     */
    public function viewAny(
        User $user,
        Session $session,
    ): bool {
        return $session->tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->tontine,
                TontinePermission::ViewSessionParticipants,
            );
    }

    /**
     * Consulter un participant précis.
     */
    public function view(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->tontine,
                TontinePermission::ViewSessionParticipants,
            );
    }

    /**
     * Ajouter un participant à une session.
     */
    public function create(
        User $user,
        Session $session,
    ): bool {
        return $session->tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->tontine,
                TontinePermission::CreateSessionParticipants,
            );
    }

    /**
     * Modifier un participant d'une session.
     */
    public function update(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->tontine,
                TontinePermission::UpdateSessionParticipants,
            );
    }

    /**
     * Retirer un participant d'une session.
     */
    public function delete(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->tontine,
                TontinePermission::RemoveSessionParticipants,
            );
    }

    /**
     * Réactiver un participant dans une session.
     */
    public function reactivate(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->tontine,
                TontinePermission::ReactivateSessionParticipants,
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

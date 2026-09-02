<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Session;
use App\Models\SessionParticipant;
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
        return $session->group->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->group,
                GroupPermission::ViewSessionParticipants,
            );
    }

    /**
     * Consulter un participant précis.
     */
    public function view(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->group,
                GroupPermission::ViewSessionParticipants,
            );
    }

    /**
     * Ajouter un participant à une session.
     */
    public function create(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->group,
                GroupPermission::CreateSessionParticipants,
            );
    }

    /**
     * Modifier un participant d'une session.
     */
    public function update(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->group,
                GroupPermission::UpdateSessionParticipants,
            );
    }

    /**
     * Retirer un participant d'une session.
     */
    public function delete(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->group,
                GroupPermission::RemoveSessionParticipants,
            );
    }

    /**
     * Réactiver un participant dans une session.
     */
    public function reactivate(
        User $user,
        SessionParticipant $participant,
    ): bool {
        return $participant->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $participant->session->group,
                GroupPermission::ReactivateSessionParticipants,
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

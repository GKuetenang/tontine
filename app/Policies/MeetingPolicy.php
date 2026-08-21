<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(
        User $user,
        Session $session,
    ): bool {
        return $session->tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->tontine,
                TontinePermission::ViewMeetings,
            );
    }

    public function view(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::ViewMeetings,
            );
    }

    public function create(
        User $user,
        Session $session,
    ): bool {
        return $session->tontine->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->tontine,
                TontinePermission::CreateMeetings,
            );
    }

    public function update(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::UpdateMeetings,
            );
    }

    public function open(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::OpenMeetings,
            );
    }

    public function close(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::CloseMeetings,
            );
    }

    public function cancel(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::CancelMeetings,
            );
    }

    public function delete(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->tontine
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->tontine,
                TontinePermission::DeleteMeetings,
            );
    }

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

<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\Tontine;
use App\Models\User;

class MeetingNotePolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewMeetingNotes,
            );
    }

    public function view(
        User $user,
        MeetingNote $note,
    ): bool {
        $tontine = $note
            ->meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::ViewMeetingNotes,
            );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::CreateMeetingNotes,
            );
    }

    public function update(
        User $user,
        MeetingNote $note,
    ): bool {
        $tontine = $note
            ->meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateMeetingNotes,
            );
    }

    public function delete(
        User $user,
        MeetingNote $note,
    ): bool {
        $tontine = $note
            ->meeting
            ->session
            ->tontine;

        return $tontine->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::DeleteMeetingNotes,
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

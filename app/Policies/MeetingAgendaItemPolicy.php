<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\Tontine;
use App\Models\User;

class MeetingAgendaItemPolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        return $this->can(
            $user,
            $meeting->session->tontine,
            TontinePermission::ViewMeetingAgenda,
        );
    }

    public function view(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->tontine,
            TontinePermission::ViewMeetingAgenda,
        );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        return $this->can(
            $user,
            $meeting->session->tontine,
            TontinePermission::CreateMeetingAgenda,
        );
    }

    public function update(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->tontine,
            TontinePermission::UpdateMeetingAgenda,
        );
    }

    public function reorder(
        User $user,
        Meeting $meeting,
    ): bool {
        $tontine = $meeting
            ->session
            ->tontine;

        return $tontine
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                tontine: $tontine,
                permission: TontinePermission::UpdateMeetingAgenda,
            );
    }

    public function delete(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->tontine,
            TontinePermission::DeleteMeetingAgenda,
        );
    }

    private function can(
        User $user,
        Tontine $tontine,
        TontinePermission $permission,
    ): bool {
        $previousTeamId = getPermissionsTeamId();

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

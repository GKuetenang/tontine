<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\User;

class MeetingAgendaItemPolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        return $this->can(
            $user,
            $meeting->session->group,
            GroupPermission::ViewMeetingAgenda,
        );
    }

    public function view(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->group,
            GroupPermission::ViewMeetingAgenda,
        );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        return $this->can(
            $user,
            $meeting->session->group,
            GroupPermission::CreateMeetingAgenda,
        );
    }

    public function update(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->group,
            GroupPermission::UpdateMeetingAgenda,
        );
    }

    public function reorder(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::UpdateMeetingAgenda,
            );
    }

    public function delete(
        User $user,
        MeetingAgendaItem $agendaItem,
    ): bool {
        return $this->can(
            $user,
            $agendaItem->meeting->session->group,
            GroupPermission::DeleteMeetingAgenda,
        );
    }

    private function can(
        User $user,
        Group $group,
        GroupPermission $permission,
    ): bool {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $group->id,
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

<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;

class MeetingNotePolicy
{
    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewMeetingNotes,
            );
    }

    public function view(
        User $user,
        MeetingNote $note,
    ): bool {
        $group = $note
            ->meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewMeetingNotes,
            );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::CreateMeetingNotes,
            );
    }

    public function update(
        User $user,
        MeetingNote $note,
    ): bool {
        $group = $note
            ->meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::UpdateMeetingNotes,
            );
    }

    public function delete(
        User $user,
        MeetingNote $note,
    ): bool {
        $group = $note
            ->meeting
            ->session
            ->group;

        return $group->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::DeleteMeetingNotes,
            );
    }

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

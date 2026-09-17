<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class MeetingNotePolicy
{
    use ChecksGroupPermissions;

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
}

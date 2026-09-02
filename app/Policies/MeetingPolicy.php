<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->group,
                GroupPermission::ViewMeetings,
            );
    }

    public function view(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::ViewMeetings,
            );
    }

    public function create(
        User $user,
        Session $session,
    ): bool {
        return $session->group->hasActiveMembership($user)
            && $this->can(
                $user,
                $session->group,
                GroupPermission::CreateMeetings,
            );
    }

    public function update(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::UpdateMeetings,
            );
    }

    public function open(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::OpenMeetings,
            );
    }

    public function close(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::CloseMeetings,
            );
    }

    public function cancel(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::CancelMeetings,
            );
    }

    public function delete(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::DeleteMeetings,
            );
    }

    public function report(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::ViewReports,
            );
    }

    public function exportReport(
        User $user,
        Meeting $meeting,
    ): bool {
        return $meeting->session->group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $meeting->session->group,
                GroupPermission::ExportReports,
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

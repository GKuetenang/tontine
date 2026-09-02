<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    /**
     * Consulter la liste des membres d’une réunion.
     */
    public function viewAny(
        User $user,
        Group $group,
    ): bool {
        return $group->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::ViewMemberships,
            );
    }

    /**
     * Consulter un membre précis.
     */
    public function view(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->group,
            GroupPermission::ViewMemberships,
        );
    }

    /**
     * Ajouter un membre à une réunion.
     */
    public function create(
        User $user,
        Group $group,
    ): bool {
        return $group->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::CreateMemberships,
            );
    }

    /**
     * Modifier un membership.
     */
    public function update(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->group,
            GroupPermission::UpdateMemberships,
        );
    }

    /**
     * Retirer un membre de la réunion.
     */
    public function delete(
        User $user,
        Membership $membership,
    ): bool {
        return $membership->group->hasActiveMembership(
            $user
        ) && $this->can(
            $user,
            $membership->group,
            GroupPermission::DeleteMemberships,
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

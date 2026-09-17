<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class GroupPolicy
{
    use ChecksGroupPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::ViewGroup);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::UpdateGroup);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::DeleteGroup);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::RestoreGroup);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::ForceDeleteGroup);
    }
}

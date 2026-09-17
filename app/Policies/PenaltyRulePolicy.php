<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\PenaltyRule;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class PenaltyRulePolicy
{
    use ChecksGroupPermissions;

    public function viewAny(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::ViewPenalties);
    }

    public function create(User $user, Group $group): bool
    {
        return $this->can($user, $group, GroupPermission::CreatePenalties);
    }

    public function update(User $user, PenaltyRule $rule): bool
    {
        return $this->can($user, $rule->group, GroupPermission::UpdatePenalties);
    }
}

<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Group;
use App\Models\PenaltyRule;
use App\Models\User;

class PenaltyRulePolicy
{
    public function viewAny(User $user, Group $group): bool
    {
        return $group->hasActiveMembership($user)
            && $user->can(GroupPermission::ViewPenalties->value);
    }

    public function create(User $user, Group $group): bool
    {
        return $group->hasActiveMembership($user)
            && $user->can(GroupPermission::CreatePenalties->value);
    }

    public function update(User $user, PenaltyRule $rule): bool
    {
        return $rule->group->hasActiveMembership($user)
            && $user->can(GroupPermission::UpdatePenalties->value);
    }
}

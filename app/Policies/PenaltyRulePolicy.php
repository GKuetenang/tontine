<?php

namespace App\Policies;

use App\Enums\TontinePermission;
use App\Models\PenaltyRule;
use App\Models\Tontine;
use App\Models\User;

class PenaltyRulePolicy
{
    public function viewAny(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::ViewPenalties->value);
    }

    public function create(User $user, Tontine $tontine): bool
    {
        return $tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::CreatePenalties->value);
    }

    public function update(User $user, PenaltyRule $rule): bool
    {
        return $rule->tontine->hasActiveMembership($user)
            && $user->can(TontinePermission::UpdatePenalties->value);
    }
}

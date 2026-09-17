<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class ContributionPolicy
{
    use ChecksGroupPermissions;

    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $group = $meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::ViewContributions,
            );
    }

    public function view(
        User $user,
        Contribution $contribution,
    ): bool {
        $group = $contribution
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::ViewContributions,
            );
    }

    public function pay(
        User $user,
        Contribution $contribution,
    ): bool {
        $group = $contribution
            ->meeting
            ->session
            ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                $user,
                $group,
                GroupPermission::RecordContributionPayments,
            );
    }
}

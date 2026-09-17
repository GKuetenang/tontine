<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class PayoutPolicy
{
    use ChecksGroupPermissions;

    public function viewAny(
        User $user,
        Meeting $meeting,
    ): bool {
        $group =
            $meeting
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::ViewPayouts,
            );
    }

    public function create(
        User $user,
        Meeting $meeting,
    ): bool {
        $group =
            $meeting
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: GroupPermission::CreatePayouts,
            );
    }

    public function update(
        User $user,
        Payout $payout,
    ): bool {
        return $this->canForPayout(
            user: $user,
            payout: $payout,
            permission: GroupPermission::UpdatePayouts,
        );
    }

    public function pay(
        User $user,
        Payout $payout,
    ): bool {
        return $this->canForPayout(
            user: $user,
            payout: $payout,
            permission: GroupPermission::PayPayouts,
        );
    }

    public function cancel(
        User $user,
        Payout $payout,
    ): bool {
        return $this->canForPayout(
            user: $user,
            payout: $payout,
            permission: GroupPermission::CancelPayouts,
        );
    }

    private function canForPayout(
        User $user,
        Payout $payout,
        GroupPermission $permission,
    ): bool {
        $group =
            $payout
                ->meeting
                ->session
                ->group;

        return $group
            ->hasActiveMembership($user)
            && $this->can(
                user: $user,
                group: $group,
                permission: $permission,
            );
    }
}

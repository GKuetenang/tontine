<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Loan;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class RepaymentPolicy
{
    use ChecksGroupPermissions;

    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session, GroupPermission::ViewRepayments);
    }

    public function create(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session, GroupPermission::CreateRepayments);
    }
}

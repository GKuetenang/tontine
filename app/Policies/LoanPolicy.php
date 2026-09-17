<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Loan;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class LoanPolicy
{
    use ChecksGroupPermissions;

    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::ViewLoans);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::CreateLoans);
    }

    public function approve(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->group, GroupPermission::ApproveLoans);
    }

    public function cancel(User $user, Loan $loan): bool
    {
        return $this->can($user, $loan->session->group, GroupPermission::DeleteLoans);
    }
}

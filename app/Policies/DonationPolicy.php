<?php

namespace App\Policies;

use App\Enums\GroupPermission;
use App\Models\Donation;
use App\Models\Session;
use App\Models\User;
use App\Policies\Concerns\ChecksGroupPermissions;

class DonationPolicy
{
    use ChecksGroupPermissions;

    public function viewAny(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::ViewDonations);
    }

    public function create(User $user, Session $session): bool
    {
        return $this->can($user, $session->group, GroupPermission::CreateDonations);
    }

    public function pay(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->group, GroupPermission::PayDonations);
    }

    public function cancel(User $user, Donation $donation): bool
    {
        return $this->can($user, $donation->session->group, GroupPermission::CancelDonations);
    }
}

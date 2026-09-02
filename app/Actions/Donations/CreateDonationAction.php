<?php

namespace App\Actions\Donations;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class CreateDonationAction
{
    public function execute(Session $session, Membership $membership, User $creator, string $amount, string $reason): Donation
    {
        if ($membership->group_id !== $session->group_id || ! $membership->isActive()) {
            throw ValidationException::withMessages(['membership_id' => __('Ce membre actif n’appartient pas à la réunion de cette session.')]);
        }

        if (! $session->participants()->active()->where('membership_id', $membership->id)->exists()) {
            throw ValidationException::withMessages(['membership_id' => __('Le bénéficiaire doit participer activement à cette session.')]);
        }

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $amount) || $this->toCents($amount) < 1) {
            throw ValidationException::withMessages(['amount' => __('Le montant doit être supérieur à zéro.')]);
        }

        $donation = new Donation;
        $donation->fill(['amount' => $amount, 'reason' => $reason, 'status' => DonationStatus::Pending]);
        $donation->session()->associate($session);
        $donation->membership()->associate($membership);
        $donation->creator()->associate($creator);
        $donation->save();

        return $donation->refresh();
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}

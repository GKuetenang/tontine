<?php

namespace App\Actions\Donations;

use App\Enums\DonationStatus;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CancelDonationAction
{
    public function execute(Donation $donation): Donation
    {
        return DB::transaction(function () use ($donation): Donation {
            $locked = Donation::query()->lockForUpdate()->findOrFail($donation->id);

            if ($locked->status !== DonationStatus::Pending) {
                throw ValidationException::withMessages(['donation' => __('Seul un don en attente peut être annulé.')]);
            }

            $locked->update(['status' => DonationStatus::Cancelled]);

            return $locked->refresh();
        });
    }
}

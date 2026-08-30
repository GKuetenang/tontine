<?php

namespace App\Actions\Payouts;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Validation\ValidationException;

final class UpdatePayoutAction
{
    public function execute(
        Payout $payout,
        string $amount,
    ): Payout {
        if (
            $payout->status
            !== PayoutStatus::Pending
        ) {
            throw ValidationException::withMessages([
                'payout' => __(
                    'Seul un versement en attente peut être modifié.'
                ),
            ]);
        }

        $payout->update([
            'amount' => $amount,
        ]);

        return $payout->refresh();
    }
}

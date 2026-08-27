<?php

namespace App\Actions\Payouts;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Validation\ValidationException;

final class UpdatePayoutAction
{
    public function execute(
        Payout $payout,
        int $amount,
    ): Payout {
        if (
            $payout->status
            !== PayoutStatus::Pending
        ) {
            throw ValidationException::withMessages([
                'payout' => __(
                    'Seul un payout en attente peut être modifié.'
                ),
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __(
                    'Le montant doit être supérieur à zéro.'
                ),
            ]);
        }

        $payout->update([
            'amount' => $amount,
        ]);

        return $payout->refresh();
    }
}

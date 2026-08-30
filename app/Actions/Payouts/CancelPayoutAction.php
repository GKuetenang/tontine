<?php

namespace App\Actions\Payouts;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Validation\ValidationException;

final class CancelPayoutAction
{
    public function execute(
        Payout $payout,
    ): Payout {
        if (
            $payout->status
            === PayoutStatus::Paid
        ) {
            throw ValidationException::withMessages([
                'payout' => __(
                    'Un versement déjà effectué ne peut pas être annulé directement.'
                ),
            ]);
        }

        if (
            $payout->status
            === PayoutStatus::Cancelled
        ) {
            return $payout;
        }

        $payout->update([
            'status' =>
            PayoutStatus::Cancelled,
        ]);

        return $payout->refresh();
    }
}

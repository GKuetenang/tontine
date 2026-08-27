<?php

namespace App\Actions\Payouts;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PayPayoutAction
{
    public function execute(
        Payout $payout,
        User $user,
    ): Payout {
        if (
            $payout->status
            !== PayoutStatus::Pending
        ) {
            throw ValidationException::withMessages([
                'payout' => __(
                    'Seul un payout en attente peut être payé.'
                ),
            ]);
        }

        return DB::transaction(
            function () use (
                $payout,
                $user,
            ): Payout {
                $payout = Payout::query()
                    ->with([
                        'meeting',
                        'drawEntry.sessionParticipant.membership',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $payout->id,
                    );

                if (
                    $payout->status
                    !== PayoutStatus::Pending
                ) {
                    throw ValidationException::withMessages([
                        'payout' => __(
                            'Ce payout a déjà été traité.'
                        ),
                    ]);
                }

                $membership =
                    $payout
                    ->drawEntry
                    ->sessionParticipant
                    ->membership;

                $transaction =
                    new Transaction();

                $transaction->fill([
                    'type' => 'payout',
                    'direction' => 'out',
                    'amount' =>
                    $payout->amount,
                    'description' => __(
                        'Versement au bénéficiaire de la tontine.'
                    ),
                    'occurred_at' =>
                    now(),
                ]);

                $transaction->session()
                    ->associate(
                        $payout
                            ->meeting
                            ->session_id,
                    );

                $transaction->membership()
                    ->associate(
                        $membership,
                    );

                $transaction->creator()
                    ->associate(
                        $user,
                    );

                $transaction
                    ->transactionable()
                    ->associate(
                        $payout,
                    );

                $transaction->save();

                $payout->update([
                    'status' =>
                    PayoutStatus::Paid,
                    'paid_at' =>
                    now(),
                ]);

                return $payout->refresh();
            },
        );
    }
}

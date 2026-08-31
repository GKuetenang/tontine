<?php

namespace App\Actions\Payouts;

use App\Enums\PayoutStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
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
        return DB::transaction(
            function () use (
                $payout,
                $user,
            ): Payout {
                $lockedPayout =
                    Payout::query()
                        ->with([
                            'meeting',
                            'drawEntry.sessionParticipant.membership',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $payout->id,
                        );

                if (
                    $lockedPayout->status
                    !== PayoutStatus::Pending
                ) {
                    throw ValidationException::withMessages([
                        'payout' => __(
                            'Ce versement a déjà été traité.'
                        ),
                    ]);
                }

                $membership =
                    $lockedPayout
                        ->drawEntry
                        ->sessionParticipant
                        ->membership;

                $transaction =
                    new Transaction;

                $transaction->fill([
                    'type' => TransactionType::Payout,

                    'direction' => TransactionDirection::Debit,

                    'amount' => $lockedPayout->amount,

                    'description' => __(
                        'Versement au bénéficiaire de la tontine.'
                    ),

                    'occurred_at' => now(),
                ]);

                $transaction->session()
                    ->associate(
                        $lockedPayout
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
                        $lockedPayout,
                    );

                $transaction->save();

                $lockedPayout->update([
                    'status' => PayoutStatus::Paid,

                    'paid_at' => now(),
                ]);

                return $lockedPayout->refresh();
            },
        );
    }
}

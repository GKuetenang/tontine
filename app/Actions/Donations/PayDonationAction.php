<?php

namespace App\Actions\Donations;

use App\Enums\DonationStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Donation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PayDonationAction
{
    public function execute(Donation $donation, User $actor): Donation
    {
        return DB::transaction(function () use ($donation, $actor): Donation {
            $locked = Donation::query()->with(['session', 'membership'])->lockForUpdate()->findOrFail($donation->id);

            if ($locked->status !== DonationStatus::Pending) {
                throw ValidationException::withMessages(['donation' => __('Ce don a déjà été traité.')]);
            }

            $transaction = new Transaction;
            $transaction->fill([
                'type' => TransactionType::Donation,
                'direction' => TransactionDirection::Debit,
                'amount' => $locked->amount,
                'description' => $locked->reason,
                'occurred_at' => now(),
            ]);
            $transaction->session()->associate($locked->session);
            $transaction->membership()->associate($locked->membership);
            $transaction->creator()->associate($actor);
            $transaction->transactionable()->associate($locked);
            $transaction->save();

            $locked->update(['status' => DonationStatus::Paid, 'paid_at' => now()]);

            return $locked->refresh();
        });
    }
}

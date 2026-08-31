<?php

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ApproveLoanAction
{
    public function execute(Loan $loan, User $approver): Loan
    {
        return DB::transaction(function () use ($loan, $approver): Loan {
            $locked = Loan::query()->with(['session', 'membership'])->lockForUpdate()->findOrFail($loan->id);
            if ($locked->status !== LoanStatus::Pending) {
                throw ValidationException::withMessages(['loan' => __('Seul un prêt en attente peut être approuvé.')]);
            }

            $transaction = new Transaction;
            $transaction->fill(['type' => TransactionType::Loan, 'direction' => TransactionDirection::Debit, 'amount' => $locked->principal_amount, 'description' => $locked->reason, 'occurred_at' => now()]);
            $transaction->session()->associate($locked->session);
            $transaction->membership()->associate($locked->membership);
            $transaction->creator()->associate($approver);
            $transaction->transactionable()->associate($locked);
            $transaction->save();
            $locked->forceFill(['status' => LoanStatus::Active, 'approved_at' => now(), 'approved_by' => $approver->id])->save();

            return $locked->refresh();
        });
    }
}

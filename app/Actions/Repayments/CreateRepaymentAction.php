<?php

namespace App\Actions\Repayments;

use App\Enums\LoanStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateRepaymentAction
{
    public function execute(Loan $loan, User $actor, string $amount): Repayment
    {
        return DB::transaction(function () use ($loan, $actor, $amount): Repayment {
            $locked = Loan::query()->with(['session', 'membership', 'repayments'])->lockForUpdate()->findOrFail($loan->id);
            if ($locked->status !== LoanStatus::Active) {
                throw ValidationException::withMessages(['repayment' => __('Seul un prêt actif peut être remboursé.')]);
            }

            $amountCents = $this->toCents($amount);
            $paidCents = $locked->repayments->sum(fn (Repayment $repayment): int => $this->toCents($repayment->amount));
            $remainingCents = $this->toCents($locked->total_due) - $paidCents;
            if ($amountCents < 1 || $amountCents > $remainingCents) {
                throw ValidationException::withMessages(['amount' => __('Le remboursement doit être positif et ne peut pas dépasser le solde restant.')]);
            }

            $interestPaidCents = $locked->repayments->sum(fn (Repayment $repayment): int => $this->toCents($repayment->interest_amount));
            $interestRemainingCents = max(0, $this->toCents($locked->interest_amount) - $interestPaidCents);
            $interestCents = min($amountCents, $interestRemainingCents);
            $repayment = new Repayment;
            $repayment->fill(['amount' => $amount, 'interest_amount' => $this->format($interestCents), 'principal_amount' => $this->format($amountCents - $interestCents), 'paid_at' => now()]);
            $repayment->loan()->associate($locked);
            $repayment->creator()->associate($actor);
            $repayment->save();

            $transaction = new Transaction;
            $transaction->fill(['type' => TransactionType::Repayment, 'direction' => TransactionDirection::Credit, 'amount' => $amount, 'description' => __('Remboursement de prêt'), 'occurred_at' => now()]);
            $transaction->session()->associate($locked->session);
            $transaction->membership()->associate($locked->membership);
            $transaction->creator()->associate($actor);
            $transaction->transactionable()->associate($repayment);
            $transaction->save();
            if ($amountCents === $remainingCents) {
                $locked->update(['status' => LoanStatus::Repaid]);
            }

            return $repayment->refresh();
        });
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw ValidationException::withMessages(['amount' => __('Le montant est invalide.')]);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function format(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}

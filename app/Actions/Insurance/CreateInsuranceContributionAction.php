<?php

namespace App\Actions\Insurance;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateInsuranceContributionAction
{
    public function execute(
        Session $session,
        Membership $membership,
        User $actor,
        string $amount,
        ?string $description = null,
        CarbonInterface|string|null $occurredAt = null,
    ): InsuranceContribution {
        if (! preg_match('/^\d+(\.\d{1,2})?$/', $amount) || $this->toCents($amount) < 1) {
            throw ValidationException::withMessages([
                'amount' => __('Le montant doit être supérieur à zéro.'),
            ]);
        }

        if ($membership->tontine_id !== $session->tontine_id || ! $membership->isActive()) {
            throw ValidationException::withMessages([
                'membership_id' => __('Ce membre actif n’appartient pas à la tontine de cette session.'),
            ]);
        }

        if (! $session->participants()->active()->where('membership_id', $membership->id)->exists()) {
            throw ValidationException::withMessages([
                'membership_id' => __('Le membre doit participer activement à cette session.'),
            ]);
        }

        $effectiveOccurredAt = $occurredAt ?? now();

        return DB::transaction(function () use ($session, $membership, $actor, $amount, $description, $effectiveOccurredAt): InsuranceContribution {
            $contribution = new InsuranceContribution;
            $contribution->fill([
                'amount' => $amount,
                'description' => $description,
                'occurred_at' => $effectiveOccurredAt,
            ]);
            $contribution->session()->associate($session);
            $contribution->membership()->associate($membership);
            $contribution->creator()->associate($actor);
            $contribution->save();

            $transaction = new Transaction;
            $transaction->fill([
                'type' => TransactionType::Insurance,
                'direction' => TransactionDirection::Credit,
                'amount' => $amount,
                'description' => $description,
                'occurred_at' => $effectiveOccurredAt,
            ]);
            $transaction->session()->associate($session);
            $transaction->membership()->associate($membership);
            $transaction->creator()->associate($actor);
            $transaction->transactionable()->associate($contribution);
            $transaction->save();

            return $contribution->refresh();
        });
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}

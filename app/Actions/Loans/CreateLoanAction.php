<?php

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class CreateLoanAction
{
    public function __construct(private readonly CalculateSimpleInterestAction $calculateInterest) {}

    public function execute(Session $session, Membership $membership, User $creator, string $principal, ?string $reason = null, ?CarbonImmutable $loanDate = null): Loan
    {
        if (! $session->participants()->active()->where('membership_id', $membership->id)->exists()) {
            throw ValidationException::withMessages(['membership_id' => __('L’emprunteur doit participer activement à cette session.')]);
        }

        if ($session->start_at === null || $session->end_at === null) {
            throw ValidationException::withMessages(['loan' => __('La session doit avoir des dates de début et de fin avant de créer un prêt.')]);
        }

        $effectiveDate = $loanDate ?? CarbonImmutable::now();
        $termMonths = $session->tontine->default_loan_term_months;
        $dueAt = $effectiveDate->addMonthsNoOverflow($termMonths);

        if ($effectiveDate->lt($session->start_at) || $dueAt->gt($session->end_at)) {
            throw ValidationException::withMessages(['loan' => __('La durée configurée place l’échéance du prêt en dehors des dates de la session.')]);
        }

        $rate = $session->tontine->default_loan_interest_rate;
        $amounts = $this->calculateInterest->execute($principal, $rate);
        $loan = new Loan;
        $loan->fill(['principal_amount' => $principal, 'interest_rate' => $rate, 'term_months' => $termMonths, 'interest_amount' => $amounts['interest'], 'total_due' => $amounts['total'], 'due_at' => $dueAt, 'reason' => $reason, 'status' => LoanStatus::Pending]);
        $loan->session()->associate($session);
        $loan->membership()->associate($membership);
        $loan->creator()->associate($creator);
        $loan->save();

        return $loan->refresh();
    }
}

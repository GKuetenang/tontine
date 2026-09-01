<?php

namespace App\Actions\Finances;

use App\Enums\LoanStatus;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\Payout;
use App\Models\Tontine;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

final class BuildTontineFinancialDashboardAction
{
    public function execute(Tontine $tontine, array $filters = []): array
    {
        $query = Transaction::query()
            ->whereHas('session', fn (Builder $query) => $query->where('tontine_id', $tontine->id))
            ->with(['membership.user', 'session:id,name']);

        $this->applyFilters($query, $filters);

        $credits = (string) (clone $query)->credits()->sum('amount');
        $debits = (string) (clone $query)->debits()->sum('amount');
        $breakdown = collect(TransactionType::cases())->map(function (TransactionType $type) use ($query): array {
            $typeQuery = (clone $query)->where('type', $type);

            return [
                'type' => $type->value,
                'label' => $type->label(),
                'credits' => $this->money((string) (clone $typeQuery)->credits()->sum('amount')),
                'debits' => $this->money((string) (clone $typeQuery)->debits()->sum('amount')),
            ];
        })->values()->all();

        return [
            'summary' => [
                'credits' => $this->money($credits),
                'debits' => $this->money($debits),
                'balance' => $this->fromCents($this->toCents($credits) - $this->toCents($debits)),
                'outstanding_loans' => $this->outstandingLoans($tontine, $filters),
            ],
            'breakdown' => $breakdown,
            'recent_transactions' => (clone $query)
                ->latest('occurred_at')
                ->limit(10)
                ->get()
                ->map(fn (Transaction $transaction): array => [
                    'id' => $transaction->id,
                    'type_label' => $transaction->type->label(),
                    'direction' => $transaction->direction->value,
                    'direction_label' => $transaction->direction->label(),
                    'amount' => $transaction->amount,
                    'member_name' => $transaction->membership?->user?->full_name,
                    'session_name' => $transaction->session->name,
                    'occurred_at' => $transaction->occurred_at->format('Y-m-d\TH:i:s'),
                ])
                ->all(),
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['session_id'] ?? null, fn (Builder $query, int|string $sessionId) => $query->where('session_id', $sessionId))
            ->when($filters['meeting_id'] ?? null, fn (Builder $query, int|string $meetingId) => $query->whereHasMorph(
                'transactionable',
                [Contribution::class, Payout::class],
                fn (Builder $query) => $query->where('meeting_id', $meetingId),
            ))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('occurred_at', '<=', $to));
    }

    private function outstandingLoans(Tontine $tontine, array $filters): string
    {
        if ($filters['meeting_id'] ?? null) {
            return '0.00';
        }

        $loans = Loan::query()
            ->whereHas('session', fn (Builder $query) => $query->where('tontine_id', $tontine->id))
            ->when($filters['session_id'] ?? null, fn (Builder $query, int|string $sessionId) => $query->where('session_id', $sessionId))
            ->where('status', LoanStatus::Active)
            ->withSum('repayments', 'amount')
            ->get();

        $cents = $loans->sum(fn (Loan $loan): int => max(
            0,
            $this->toCents($loan->total_due) - $this->toCents((string) ($loan->repayments_sum_amount ?? '0')),
        ));

        return $this->fromCents($cents);
    }

    private function money(string $amount): string
    {
        return $this->fromCents($this->toCents($amount));
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }
}

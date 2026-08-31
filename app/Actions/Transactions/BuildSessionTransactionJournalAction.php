<?php

namespace App\Actions\Transactions;

use App\Data\TransactionData;
use App\Models\Session;
use Illuminate\Database\Eloquent\Builder;

final class BuildSessionTransactionJournalAction
{
    /** @return array{collection: mixed, summary: array{credits: string, debits: string, balance: string}} */
    public function execute(Session $session, array $filters): array
    {
        $query = $session->transactions()->with(['membership.user', 'creator']);

        $query->when($filters['direction'] ?? null, fn (Builder $query, string $value) => $query->where('direction', $value))
            ->when($filters['type'] ?? null, fn (Builder $query, string $value) => $query->where('type', $value))
            ->when($filters['from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('occurred_at', '>=', $value))
            ->when($filters['to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('occurred_at', '<=', $value));

        $credits = (string) (clone $query)->credits()->sum('amount');
        $debits = (string) (clone $query)->debits()->sum('amount');

        return [
            'collection' => TransactionData::collect($query->latest('occurred_at')->paginate(20)->withQueryString()),
            'summary' => [
                'credits' => $this->formatMoney($credits),
                'debits' => $this->formatMoney($debits),
                'balance' => $this->fromCents($this->toCents($credits) - $this->toCents($debits)),
            ],
        ];
    }

    private function formatMoney(string $amount): string
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

<?php

namespace App\Actions\Insurance;

use App\Data\InsuranceContributionData;
use App\Models\Session;

final class BuildInsuranceJournalAction
{
    public function execute(Session $session, array $filters = []): array
    {
        $q = trim($filters['q'] ?? '');
        $contributions = $session->insuranceContributions()
            ->with(['creator', 'membership.user'])
            ->when($q, fn ($query) => $query->whereHas('membership.user', fn ($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")))
            ->orderBy($filters['sort'] ?? 'occurred_at', $filters['dir'] ?? 'desc')
            ->paginate()
            ->withQueryString();
        $total = 0;

        $session->insuranceContributions()
            ->select(['amount'])
            ->each(function ($entry) use (&$total): void {
                $total += $this->toCents($entry->amount);
            });

        return [
            'collection' => InsuranceContributionData::collect($contributions),
            'summary' => [
                'total' => $this->fromCents($total),
                'contributions_count' => $session->insuranceContributions()->count(),
                'contributors_count' => $session->insuranceContributions()->distinct()->count('membership_id'),
            ],
        ];
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function fromCents(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absolute = abs($amount);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }
}

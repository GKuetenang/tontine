<?php

namespace App\Actions\Draws;

use App\Enums\DrawAllocationMode;
use App\Models\Session;
use Illuminate\Validation\ValidationException;

final class DetermineDrawEntriesCountAction
{
    public function execute(
        Session $session,
        int $contributionAmount,
        ?int $customCount = null,
    ): int {
        return match ($session->draw_allocation_mode) {
            DrawAllocationMode::OnePerMember => 1,

            DrawAllocationMode::BasedOnContribution => $this->fromContribution(
                session: $session,
                contributionAmount: $contributionAmount,
            ),

            DrawAllocationMode::Custom => $customCount
                ?? throw ValidationException::withMessages([
                    'draw_entries_count' => __(
                        'Le nombre de tours est obligatoire.'
                    ),
                ]),
        };
    }

    private function fromContribution(
        Session $session,
        int $contributionAmount,
    ): int {
        $baseAmount = $session->base_contribution_amount;

        if (! $baseAmount || $baseAmount < 1) {
            throw ValidationException::withMessages([
                'base_contribution_amount' => __(
                    'Le montant de base doit être défini.'
                ),
            ]);
        }

        if ($contributionAmount % $baseAmount !== 0) {
            throw ValidationException::withMessages([
                'contribution_amount' => __(
                    'Le montant doit être un multiple du montant de base.'
                ),
            ]);
        }

        return intdiv(
            $contributionAmount,
            $baseAmount,
        );
    }
}

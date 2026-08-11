<?php

namespace App\Actions\Sessions;

use App\Enums\DrawAllocationMode;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSessionAction
{
    public function execute(
        Session $session,
        array $attributes,
    ): Session {
        return DB::transaction(function () use (
            $session,
            $attributes,
        ): Session {
            if ($session->isClosed()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une session fermée ne peut plus être modifiée.'
                    ),
                ]);
            }

            $session->fill([
                'name' =>
                $attributes['name'] ?? $session->name,

                'description' =>
                $attributes['description']
                    ?? $session->description,

                'start_at' =>
                $attributes['start_at']
                    ?? $session->start_at,

                'end_at' =>
                $attributes['end_at']
                    ?? $session->end_at,
            ]);

            if (! $session->isActive()) {
                $allocationMode =
                    isset($attributes['draw_allocation_mode'])
                    ? DrawAllocationMode::from(
                        $attributes['draw_allocation_mode']
                    )
                    : $session->draw_allocation_mode;

                $baseContributionAmount =
                    $attributes['base_contribution_amount']
                    ?? $session->base_contribution_amount;

                $this->validateDrawConfiguration(
                    allocationMode: $allocationMode,
                    baseContributionAmount: $baseContributionAmount,
                );

                $session->fill([
                    'default_contribution_amount' =>
                    $attributes['default_contribution_amount']
                        ?? $session->default_contribution_amount,

                    'draw_allocation_mode' =>
                    $allocationMode,

                    'base_contribution_amount' =>
                    $baseContributionAmount,
                ]);
            }

            $session->save();

            return $session->refresh();
        });
    }

    private function validateDrawConfiguration(
        DrawAllocationMode $allocationMode,
        ?int $baseContributionAmount,
    ): void {
        if (
            $allocationMode ===
            DrawAllocationMode::BasedOnContribution
            && (
                $baseContributionAmount === null
                || $baseContributionAmount < 1
            )
        ) {
            throw ValidationException::withMessages([
                'base_contribution_amount' => __(
                    'Le montant de base est obligatoire lorsque le nombre de tours dépend de la cotisation.'
                ),
            ]);
        }
    }
}

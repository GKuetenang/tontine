<?php

namespace App\Actions\Sessions;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Session;
use App\Models\Tontine;
use App\Support\UniqueSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateSessionAction
{
    public function __construct(
        private readonly UniqueSlug $uniqueSlug
    ) {}

    /**
     * @param array{
     *     name: string,
     *     description?: string|null,
     *     start_at: string,
     *     end_at?: string|null
     * } $attributes
     */
    public function execute(
        Tontine $tontine,
        array $attributes,
    ): Session {
        return DB::transaction(function () use (
            $tontine,
            $attributes,
        ): Session {
            $allocationMode = DrawAllocationMode::from(
                $attributes['draw_allocation_mode']
                    ?? DrawAllocationMode::OnePerMember->value
            );

            $defaultContributionAmount =
                $attributes['default_contribution_amount']
                ?? $tontine->default_contribution_amount;

            $baseContributionAmount =
                $attributes['base_contribution_amount']
                ?? null;

            $session = new Session;

            $session->tontine()->associate($tontine);

            $this->validateDrawConfiguration(
                allocationMode: $allocationMode,
                baseContributionAmount: $baseContributionAmount,
            );

            $session->fill([
                'status' => SessionStatus::Draft,
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'default_contribution_amount' => $defaultContributionAmount,
                'draw_allocation_mode' => $allocationMode,
                'base_contribution_amount' => $baseContributionAmount,
                'beneficiaries_per_meeting' => $attributes['beneficiaries_per_meeting'] ?? 1,
                'start_at' => $attributes['start_at'] ?? null,
                'end_at' => $attributes['end_at'] ?? null,
            ]);

            $session->slug = $this->uniqueSlug->generate(
                query: $tontine->sessions()->getQuery(),
                value: $attributes['name'],
            );

            $session->save();

            return $session;
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

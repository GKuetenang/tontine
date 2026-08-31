<?php

namespace App\Actions\SessionParticipants;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSessionParticipantAction
{
    public function execute(
        SessionParticipant $participant,
        int $contributionAmount,
        ?int $drawEntriesCount = null,
    ): SessionParticipant {
        return DB::transaction(function () use (
            $participant,
            $contributionAmount,
            $drawEntriesCount,
        ): SessionParticipant {
            $participant->loadMissing('session');

            $session = $participant->session;

            if ($session->status !== SessionStatus::Draft) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Les paramètres d’un participant ne peuvent être modifiés que lorsque la session est en préparation.'
                    ),
                ]);
            }

            if (! $participant->is_active) {
                throw ValidationException::withMessages([
                    'participant' => __(
                        'Un participant inactif ne peut pas être modifié.'
                    ),
                ]);
            }

            if ($contributionAmount < 1) {
                throw ValidationException::withMessages([
                    'contribution_amount' => __(
                        'Le montant de la cotisation doit être supérieur à zéro.'
                    ),
                ]);
            }

            $entriesCount = match ($session->draw_allocation_mode) {
                DrawAllocationMode::OnePerMember => 1,

                DrawAllocationMode::BasedOnContribution => $this->entriesFromContribution(
                    $session->base_contribution_amount,
                    $contributionAmount,
                ),

                DrawAllocationMode::Custom => $this->validateCustomCount(
                    $drawEntriesCount,
                ),
            };

            $participant->fill([
                'contribution_amount' => $contributionAmount,

                'draw_entries_count' => $entriesCount,
            ]);

            $participant->save();

            return $participant->refresh();
        });
    }

    private function entriesFromContribution(
        ?int $baseAmount,
        int $contributionAmount,
    ): int {
        if ($baseAmount === null || $baseAmount < 1) {
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

    private function validateCustomCount(
        ?int $count,
    ): int {
        if ($count === null || $count < 1) {
            throw ValidationException::withMessages([
                'draw_entries_count' => __(
                    'Le nombre de droits au tirage doit être supérieur à zéro.'
                ),
            ]);
        }

        return $count;
    }
}

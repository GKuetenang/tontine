<?php

namespace App\Actions\Sessions;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Session;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ActivateSessionAction
{
    public function execute(
        Session $session,
    ): Session {
        return DB::transaction(function () use (
            $session,
        ): Session {
            $session->loadMissing('participants');

            if (! $session->isDraft()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Seule une session en préparation peut être activée.'
                    ),
                ]);
            }

            if ($session->isActive()) {
                return $session;
            }

            $hasActiveSession = Session::query()
                ->where('group_id', $session->group_id)
                ->where('status', SessionStatus::Active)
                ->whereKeyNot($session->id)
                ->exists();

            if ($hasActiveSession) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une autre session est déjà active pour cette réunion.'
                    ),
                ]);
            }

            $this->validateParticipants($session);
            $this->validateDrawConfiguration($session);

            $session->forceFill([
                'status' => SessionStatus::Active,
                'activated_at' => now(),
            ]);

            $session->save();

            return $session->refresh();
        });
    }

    private function validateParticipants(
        Session $session,
    ): void {
        $participants = $session
            ->participants()
            ->where('is_active', true)
            ->get();

        if ($participants->isEmpty()) {
            throw ValidationException::withMessages([
                'participants' => __(
                    'La session doit avoir au moins un participant actif avant son activation.'
                ),
            ]);
        }

        foreach ($participants as $participant) {
            if ($participant->contribution_amount < 1) {
                throw ValidationException::withMessages([
                    'participants' => __(
                        'Chaque participant doit avoir un montant de cotisation valide.'
                    ),
                ]);
            }

            if ($participant->draw_entries_count < 1) {
                throw ValidationException::withMessages([
                    'participants' => __(
                        'Chaque participant doit avoir au moins un droit au tirage.'
                    ),
                ]);
            }
        }
    }

    private function validateDrawConfiguration(
        Session $session,
    ): void {
        if (
            $session->draw_allocation_mode ===
            DrawAllocationMode::BasedOnContribution
            && (
                ! $session->base_contribution_amount
                || $session->base_contribution_amount < 1
            )
        ) {
            throw ValidationException::withMessages([
                'base_contribution_amount' => __(
                    'Le montant de base doit être défini avant l’activation de la session.'
                ),
            ]);
        }
    }

    private function validateParticipantDrawEntries(
        Session $session,
        SessionParticipant $participant,
    ): void {
        if (
            $session->draw_allocation_mode
            !== DrawAllocationMode::BasedOnContribution
        ) {
            return;
        }

        $baseAmount = $session->base_contribution_amount;

        if (
            $participant->contribution_amount
            % $baseAmount !== 0
        ) {
            throw ValidationException::withMessages([
                'participants' => __(
                    'Les cotisations doivent être des multiples du montant de base.'
                ),
            ]);
        }

        $expectedCount = intdiv(
            $participant->contribution_amount,
            $baseAmount,
        );

        if (
            $participant->draw_entries_count
            !== $expectedCount
        ) {
            throw ValidationException::withMessages([
                'participants' => __(
                    'Le nombre de droits au tirage est incohérent avec le montant de cotisation.'
                ),
            ]);
        }
    }
}

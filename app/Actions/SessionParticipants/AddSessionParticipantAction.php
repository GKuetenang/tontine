<?php

namespace App\Actions\SessionParticipants;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddSessionParticipantAction
{
    public function execute(
        Session $session,
        Membership $membership,
        ?int $contributionAmount = null,
        ?int $drawEntriesCount = null,
    ): SessionParticipant {
        return DB::transaction(function () use (
            $session,
            $membership,
            $contributionAmount,
            $drawEntriesCount,
        ): SessionParticipant {
            $this->ensureSessionIsConfigurable($session);
            $this->ensureMembershipBelongsToTontine(
                $session,
                $membership,
            );
            $this->ensureMembershipIsActive($membership);

            $existing = SessionParticipant::query()
                ->where('session_id', $session->id)
                ->where('membership_id', $membership->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Ce membre participe déjà à cette session.'
                    ),
                ]);
            }

            $amount = $contributionAmount
                ?? $session->default_contribution_amount;

            if ($amount === null || $amount < 1) {
                throw ValidationException::withMessages([
                    'contribution_amount' => __(
                        'Le montant de la cotisation est obligatoire.'
                    ),
                ]);
            }

            $entriesCount = $this->determineDrawEntriesCount(
                session: $session,
                contributionAmount: $amount,
                customCount: $drawEntriesCount,
            );

            $participant = new SessionParticipant();

            $participant->session()->associate($session);
            $participant->membership()->associate($membership);

            $participant->fill([
                'contribution_amount' => $amount,
                'draw_entries_count' => $entriesCount,
            ]);

            $participant->forceFill([
                'is_active' => true,
                'joined_at' => now(),
                'left_at' => null,
            ]);

            $participant->save();

            return $participant->refresh();
        });
    }

    private function ensureSessionIsConfigurable(
        Session $session,
    ): void {
        if ($session->status !== SessionStatus::Draft) {
            throw ValidationException::withMessages([
                'session' => __(
                    'Les participants ne peuvent être ajoutés que lorsque la session est en préparation.'
                ),
            ]);
        }
    }

    private function ensureMembershipBelongsToTontine(
        Session $session,
        Membership $membership,
    ): void {
        if ($membership->tontine_id !== $session->tontine_id) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Ce membre n’appartient pas à cette tontine.'
                ),
            ]);
        }
    }

    private function ensureMembershipIsActive(
        Membership $membership,
    ): void {
        if (! $membership->isActive()) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Seul un membre actif peut participer à une session.'
                ),
            ]);
        }
    }

    private function determineDrawEntriesCount(
        Session $session,
        int $contributionAmount,
        ?int $customCount,
    ): int {
        return match ($session->draw_allocation_mode) {
            DrawAllocationMode::OnePerMember => 1,

            DrawAllocationMode::BasedOnContribution =>
            $this->entriesFromContribution(
                $session,
                $contributionAmount,
            ),

            DrawAllocationMode::Custom =>
            $this->validateCustomCount($customCount),
        };
    }

    private function entriesFromContribution(
        Session $session,
        int $contributionAmount,
    ): int {
        $baseAmount = $session->base_contribution_amount;

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
                    'Le montant de la cotisation doit être un multiple du montant de base.'
                ),
            ]);
        }

        return intdiv(
            $contributionAmount,
            $baseAmount,
        );
    }

    private function validateCustomCount(
        ?int $customCount,
    ): int {
        if ($customCount === null || $customCount < 1) {
            throw ValidationException::withMessages([
                'draw_entries_count' => __(
                    'Le nombre de droits au tirage est obligatoire.'
                ),
            ]);
        }

        return $customCount;
    }
}

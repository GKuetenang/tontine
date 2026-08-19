<?php

namespace App\Actions\Draws;

use App\Enums\SessionStatus;
use App\Models\Draw;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ConfirmDrawAction
{
    public function execute(
        Draw $draw,
        User $user,
    ): Draw {
        return DB::transaction(function () use (
            $draw,
            $user,
        ): Draw {
            if ($draw->isConfirmed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Ce tirage est déjà confirmé.'
                    ),
                ]);
            }

            if (
                $draw->session->status
                !== SessionStatus::Active
            ) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Le tirage ne peut être confirmé que pour une session active.'
                    ),
                ]);
            }

            $expectedEntries = (int)$draw
                ->session
                ->participants()
                ->active()
                ->sum('draw_entries_count');

            $actualEntries = $draw
                ->entries()
                ->count();

            if ($actualEntries === 0) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Le tirage ne contient aucune entrée.'
                    ),
                ]);
            }

            if ($actualEntries !== $expectedEntries) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Le nombre d’entrées du tirage est invalide.'
                    ),
                ]);
            }

            $positions = $draw
                ->entries()
                ->orderBy('position')
                ->pluck('position')
                ->all();

            if (
                $positions
                !== range(1, $expectedEntries)
            ) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Les positions du tirage sont invalides.'
                    ),
                ]);
            }

            $draw->forceFill([
                'confirmed_by' => $user->id,
                'confirmed_at' => now(),
            ])->save();

            return $draw
                ->refresh()
                ->load([
                    'entries.sessionParticipant.membership.user',
                    'creator',
                    'confirmer',
                ]);
        });
    }
}

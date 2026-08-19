<?php

namespace App\Actions\Draws;

use App\Enums\SessionStatus;
use App\Models\Draw;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class GenerateDrawAction
{
    public function execute(
        Session $session,
        User $user,
    ): Draw {
        return DB::transaction(function () use (
            $session,
            $user,
        ): Draw {
            if ($session->status !== SessionStatus::Active) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Le tirage ne peut être généré que pour une session active.'
                    ),
                ]);
            }

            $participants = $session
                ->participants()
                ->active()
                ->get();

            if ($participants->isEmpty()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Aucun participant actif n’est disponible.'
                    ),
                ]);
            }

            $draw = $session
                ->draw()
                ->withTrashed()
                ->first();

            if ($draw?->isConfirmed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Un tirage confirmé ne peut plus être régénéré.'
                    ),
                ]);
            }

            if ($draw?->trashed()) {
                $draw->restore();
            }

            if (! $draw) {
                $draw = $session
                    ->draw()
                    ->create([
                        'created_by' => $user->id,
                    ]);
            }

            $tickets = $participants
                ->flatMap(
                    function (
                        SessionParticipant $participant,
                    ): array {
                        return collect(
                            range(
                                1,
                                $participant->draw_entries_count,
                            ),
                        )
                            ->map(
                                fn(int $entryNumber): array => [
                                    'session_participant_id' =>
                                    $participant->id,

                                    'entry_number' =>
                                    $entryNumber,
                                ],
                            )
                            ->all();
                    },
                )
                ->shuffle()
                ->values();

            $draw->entries()->delete();

            foreach ($tickets as $index => $ticket) {
                $draw->entries()->create([
                    'session_participant_id' =>
                    $ticket['session_participant_id'],

                    'entry_number' =>
                    $ticket['entry_number'],

                    'position' => $index + 1,
                ]);
            }

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

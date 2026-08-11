<?php

namespace App\Actions\Draws;

use App\Enums\DrawMode;
use App\Models\Draw;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class GenerateDrawAction
{
    public function execute(Draw $draw): Draw
    {
        return DB::transaction(function () use ($draw): Draw {
            if ($draw->isConfirmed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Un tirage confirmé ne peut plus être régénéré.'
                    ),
                ]);
            }

            $participants = $draw
                ->session
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

            $existing = $draw->session
                ->draw()
                ->withTrashed()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Un tirage existe déjà pour cette session.'
                    ),
                ]);
            }

            $tickets = $participants
                ->flatMap(
                    function (
                        SessionParticipant $participant,
                    ): array {
                        return collect(
                            range(1, $participant->shares_count)
                        )
                            ->map(
                                fn(int $shareNumber): array => [
                                    'participant_id' =>
                                    $participant->id,

                                    'share_number' =>
                                    $shareNumber,
                                ],
                            )
                            ->all();
                    },
                )
                ->shuffle()
                ->values();

            $draw->entries()->delete();

            // $sharesCount = match ($draw->session->draw_mode) {
            //     DrawMode::PerParticipant => 1,
            //     DrawMode::PerShare => $participant->shares_count,
            // };

            foreach ($tickets as $index => $ticket) {
                $draw->entries()->create([
                    'session_participant_id' =>
                    $ticket['participant_id'],
                    'share_number' =>
                    $ticket['share_number'],
                    'position' => $index + 1,
                ]);
            }

            return $draw
                ->refresh()
                ->load([
                    'entries.participant.membership.user',
                ]);
        });
    }
}

<?php

namespace App\Actions\Draws;

use App\Enums\SessionStatus;
use App\Models\Draw;
use App\Models\DrawEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SwapDrawEntriesAction
{
    public function execute(
        Draw $draw,
        DrawEntry $source,
        DrawEntry $target,
    ): void {
        if (
            $draw->session->status
            !== SessionStatus::Active
        ) {
            throw ValidationException::withMessages([
                'draw' => __(
                    'Le tirage ne peut être modifié que pendant une session active.'
                ),
            ]);
        }

        if ($draw->confirmed_at !== null) {
            throw ValidationException::withMessages([
                'draw' => __(
                    'Un tirage confirmé ne peut plus être modifié.'
                ),
            ]);
        }

        if (
            $source->draw_id !== $draw->id
            || $target->draw_id !== $draw->id
        ) {
            throw ValidationException::withMessages([
                'draw' => __(
                    'Les entrées doivent appartenir au tirage courant.'
                ),
            ]);
        }

        if ($source->is($target)) {
            return;
        }

        DB::transaction(
            function () use (
                $draw,
                $source,
                $target,
            ): void {
                $entries = DrawEntry::query()
                    ->where(
                        'draw_id',
                        $draw->id,
                    )
                    ->whereKey([
                        $source->id,
                        $target->id,
                    ])
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                /** @var DrawEntry|null $lockedSource */
                $lockedSource =
                    $entries->get(
                        $source->id,
                    );

                /** @var DrawEntry|null $lockedTarget */
                $lockedTarget =
                    $entries->get(
                        $target->id,
                    );

                if (
                    ! $lockedSource
                    || ! $lockedTarget
                ) {
                    throw ValidationException::withMessages([
                        'draw' => __(
                            'Impossible de retrouver les entrées à permuter.'
                        ),
                    ]);
                }

                $sourcePosition =
                    $lockedSource->position;

                $targetPosition =
                    $lockedTarget->position;

                /*
                 * position est UNSIGNED.
                 * On utilise donc une position temporaire
                 * supérieure à la position maximale.
                 */
                $temporaryPosition =
                    DrawEntry::query()
                        ->where(
                            'draw_id',
                            $draw->id,
                        )
                        ->max('position')
                    + 1;

                /*
                 * Exemple :
                 *
                 * 1 → 8
                 * 7 → 1
                 * 8 → 7
                 */

                $lockedSource
                    ->forceFill([
                        'position' => $temporaryPosition,
                    ])
                    ->save();

                $lockedTarget
                    ->forceFill([
                        'position' => $sourcePosition,
                    ])
                    ->save();

                $lockedSource
                    ->forceFill([
                        'position' => $targetPosition,
                    ])
                    ->save();
            },
        );
    }
}

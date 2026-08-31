<?php

namespace App\Actions\MeetingAgendaItems;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderMeetingAgendaItemsAction
{
    /**
     * @param  array<int>  $itemIds
     */
    public function execute(
        Meeting $meeting,
        array $itemIds,
    ): void {
        DB::transaction(function () use (
            $meeting,
            $itemIds,
        ): void {
            if (
                $meeting->status
                !== MeetingStatus::Scheduled
            ) {
                throw ValidationException::withMessages([
                    'agenda' => __(
                        'L’ordre du jour ne peut être réorganisé que pour une réunion prévue.'
                    ),
                ]);
            }

            $existingIds = $meeting
                ->agendaItems()
                ->pluck('id')
                ->sort()
                ->values()
                ->all();

            $submittedIds = collect($itemIds)
                ->sort()
                ->values()
                ->all();

            if ($existingIds !== $submittedIds) {
                throw ValidationException::withMessages([
                    'agenda_items' => __(
                        'La liste des points de l’ordre du jour est invalide.'
                    ),
                ]);
            }

            /*
             * Première passe avec des positions temporaires
             * pour éviter les collisions avec la contrainte unique.
             */
            foreach ($itemIds as $index => $itemId) {
                $meeting
                    ->agendaItems()
                    ->whereKey($itemId)
                    ->update([
                        'position' => 10_000 + $index,
                    ]);
            }

            /*
             * Deuxième passe vers les positions définitives.
             */
            foreach ($itemIds as $index => $itemId) {
                $meeting
                    ->agendaItems()
                    ->whereKey($itemId)
                    ->update([
                        'position' => $index + 1,
                    ]);
            }
        });
    }
}

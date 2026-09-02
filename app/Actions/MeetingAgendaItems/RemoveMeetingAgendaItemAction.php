<?php

namespace App\Actions\MeetingAgendaItems;

use App\Enums\MeetingStatus;
use App\Models\MeetingAgendaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RemoveMeetingAgendaItemAction
{
    public function execute(
        MeetingAgendaItem $item,
    ): void {
        DB::transaction(function () use ($item): void {
            $meeting = $item->meeting;

            if (
                $meeting->status
                !== MeetingStatus::Scheduled
            ) {
                throw ValidationException::withMessages([
                    'agenda' => __(
                        'L’ordre du jour ne peut être modifié que pour une assise prévue.'
                    ),
                ]);
            }

            $item->delete();

            $meeting
                ->agendaItems()
                ->get()
                ->each(
                    fn (
                        MeetingAgendaItem $agendaItem,
                        int $index,
                    ) => $agendaItem->update([
                        'position' => $index + 1,
                    ]),
                );
        });
    }
}

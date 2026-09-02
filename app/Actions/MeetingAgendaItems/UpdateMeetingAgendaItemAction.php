<?php

namespace App\Actions\MeetingAgendaItems;

use App\Enums\MeetingStatus;
use App\Models\MeetingAgendaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateMeetingAgendaItemAction
{
    public function execute(
        MeetingAgendaItem $item,
        string $title,
        ?string $description = null,
    ): MeetingAgendaItem {
        return DB::transaction(
            function () use (
                $item,
                $title,
                $description,
            ): MeetingAgendaItem {
                if (
                    $item->meeting->status
                    !== MeetingStatus::Scheduled
                ) {
                    throw ValidationException::withMessages([
                        'agenda' => __(
                            'L’ordre du jour ne peut être modifié que pour une assise prévue.'
                        ),
                    ]);
                }

                $item->update([
                    'title' => $title,
                    'description' => $description,
                ]);

                return $item->refresh();
            },
        );
    }
}

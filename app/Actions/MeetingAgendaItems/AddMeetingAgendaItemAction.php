<?php

namespace App\Actions\MeetingAgendaItems;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AddMeetingAgendaItemAction
{
    public function execute(
        Meeting $meeting,
        string $title,
        ?string $description = null,
    ): MeetingAgendaItem {
        return DB::transaction(
            function () use (
                $meeting,
                $title,
                $description,
            ): MeetingAgendaItem {
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

                $position = (
                    $meeting
                        ->agendaItems()
                        ->max('position') ?? 0
                ) + 1;

                $item = new MeetingAgendaItem;

                $item->fill([
                    'title' => $title,
                    'description' => $description,
                    'position' => $position,
                ]);

                $item->meeting()
                    ->associate($meeting);

                $item->save();

                return $item->refresh();
            },
        );
    }
}

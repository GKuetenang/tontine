<?php

namespace App\Actions\Draws;

use App\Models\DrawEntry;
use App\Models\Meeting;

final class ResolveDrawEntryMeetingAction
{
    public function execute(
        DrawEntry $entry,
    ): ?Meeting {
        $draw = $entry->draw;

        $session = $draw->session;

        $beneficiariesPerMeeting =
            max(
                1,
                $session->beneficiaries_per_meeting,
            );

        /*
         * Positions:
         *
         * 1 bénéficiaire/assise:
         * 1 -> index 0
         * 2 -> index 1
         *
         * 2 bénéficiaires/assise:
         * 1 -> index 0
         * 2 -> index 0
         * 3 -> index 1
         * 4 -> index 1
         */
        $meetingIndex = intdiv(
            $entry->position - 1,
            $beneficiariesPerMeeting,
        );

        return $session
            ->meetings()
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->skip($meetingIndex)
            ->first();
    }
}

<?php

namespace App\Support;

use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Session;
use Illuminate\Support\Collection;

final class DrawCalendar
{
    /**
     * Retourne le numéro d’assise auquel
     * une position du tirage est rattachée.
     *
     * Ex:
     * beneficiaries_per_meeting = 2
     *
     * position 1 -> meeting #1
     * position 2 -> meeting #1
     * position 3 -> meeting #2
     * position 4 -> meeting #2
     */
    public function meetingNumberForPosition(
        Session $session,
        int $position,
    ): int {
        $beneficiariesPerMeeting =
            max(
                1,
                $session->beneficiaries_per_meeting,
            );

        return intdiv(
            $position - 1,
            $beneficiariesPerMeeting,
        ) + 1;
    }

    /**
     * @param  Collection<int, Meeting>  $meetings
     */
    public function meetingForEntry(
        Session $session,
        DrawEntry $entry,
        Collection $meetings,
    ): ?Meeting {
        $meetingNumber =
            $this->meetingNumberForPosition(
                session: $session,
                position: $entry->position,
            );

        return $meetings->get(
            $meetingNumber,
        );
    }

    /**
     * Retourne les positions prévues
     * pour une assise donnée.
     *
     * Ex:
     * beneficiaries_per_meeting = 2
     *
     * meeting #1 -> positions 1,2
     * meeting #2 -> positions 3,4
     */
    public function positionsForMeeting(
        Session $session,
        Meeting $meeting,
    ): array {
        $beneficiariesPerMeeting =
            max(
                1,
                $session->beneficiaries_per_meeting,
            );

        $firstPosition =
            (($meeting->number - 1)
                * $beneficiariesPerMeeting)
            + 1;

        return range(
            $firstPosition,
            $firstPosition
                + $beneficiariesPerMeeting
                - 1,
        );
    }

    /**
     * @param  Collection<int, DrawEntry>  $entries
     * @return Collection<int, DrawEntry>
     */
    public function entriesForMeeting(
        Session $session,
        Meeting $meeting,
        Collection $entries,
    ): Collection {
        $positions =
            $this->positionsForMeeting(
                session: $session,
                meeting: $meeting,
            );

        return $entries
            ->whereIn(
                'position',
                $positions,
            )
            ->sortBy('position')
            ->values();
    }
}

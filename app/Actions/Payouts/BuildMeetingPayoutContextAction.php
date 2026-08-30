<?php

namespace App\Actions\Payouts;

use App\Data\MeetingPayoutContextData;
use App\Data\PayoutCandidateData;
use App\Models\Draw;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Support\DrawCalendar;

final class BuildMeetingPayoutContextAction
{
    public function __construct(
        private readonly DrawCalendar $calendar,
    ) {}

    public function execute(
        Meeting $meeting,
    ): MeetingPayoutContextData {
        $session =
            $meeting->session;

        $draw = Draw::query()
            ->where(
                'session_id',
                $session->id,
            )
            ->whereNotNull(
                'confirmed_at'
            )
            ->with([
                'entries.sessionParticipant.membership.user',
                'entries.payout',
            ])
            ->first();

        /*
         * Aucun tirage confirmé.
         */
        if (! $draw) {
            return new MeetingPayoutContextData(
                expected: [],
                available: [],
            );
        }

        $entries =
            $draw->entries
            ->sortBy('position')
            ->values();

        /*
         * DrawEntries normalement prévus
         * pour ce Meeting.
         */
        $expectedEntries =
            $this->calendar
            ->entriesForMeeting(
                session: $session,
                meeting: $meeting,
                entries: $entries,
            );

        /*
         * Un DrawEntry ayant déjà un Payout
         * n'est plus disponible.
         */
        $availableEntries =
            $entries
            ->filter(
                fn(DrawEntry $entry): bool =>
                $entry->payout === null,
            )
            ->values();

        $expectedIds =
            $expectedEntries
            ->pluck('id')
            ->all();

        $expected =
            $expectedEntries
            ->filter(
                fn(DrawEntry $entry): bool =>
                $entry->payout === null,
            )
            ->map(
                fn(DrawEntry $entry) =>
                PayoutCandidateData::fromModel(
                    entry: $entry,
                    expected: true,
                ),
            )
            ->values()
            ->all();

        $available =
            $availableEntries
            ->map(
                fn(DrawEntry $entry) =>
                PayoutCandidateData::fromModel(
                    entry: $entry,
                    expected: in_array(
                        $entry->id,
                        $expectedIds,
                        true,
                    ),
                ),
            )
            ->values()
            ->all();

        return new MeetingPayoutContextData(
            expected: $expected,
            available: $available,
        );
    }
}

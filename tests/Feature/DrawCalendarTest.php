<?php

use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Session;
use App\Support\DrawCalendar;

it(
    'maps one beneficiary per meeting',
    function (): void {
        $session =
            new Session([
                'beneficiaries_per_meeting' => 1,
            ]);

        $entry =
            new DrawEntry([
                'position' => 3,
            ]);

        $meetings =
            collect([
                new Meeting([
                    'number' => 1,
                ]),

                new Meeting([
                    'number' => 2,
                ]),

                new Meeting([
                    'number' => 3,
                ]),
            ]);

        $meeting =
            app(
                DrawCalendar::class,
            )->meetingForEntry(
                session: $session,
                entry: $entry,
                meetings: $meetings->keyBy(
                    'number',
                ),
            );

        expect(
            $meeting?->number,
        )->toBe(3);
    },
);

it(
    'maps several beneficiaries to the same meeting',
    function (): void {
        $session =
            new Session([
                'beneficiaries_per_meeting' => 2,
            ]);

        $meetings =
            collect([
                new Meeting([
                    'number' => 1,
                ]),

                new Meeting([
                    'number' => 2,
                ]),

                new Meeting([
                    'number' => 3,
                ]),
            ])->keyBy('number');

        $calendar =
            app(
                DrawCalendar::class,
            );

        foreach (
            [
                1 => 1,
                2 => 1,
                3 => 2,
                4 => 2,
                5 => 3,
                6 => 3,
            ] as $position => $expectedMeetingNumber
        ) {
            $entry =
                new DrawEntry([
                    'position' => $position,
                ]);

            $meeting =
                $calendar
                    ->meetingForEntry(
                        session: $session,

                        entry: $entry,

                        meetings: $meetings,
                    );

            expect(
                $meeting?->number,
            )->toBe(
                $expectedMeetingNumber,
            );
        }
    },
);

it(
    'returns no meeting when the draw position exceeds the planned meetings',
    function (): void {
        $session =
            new Session([
                'beneficiaries_per_meeting' => 2,
            ]);

        $entry =
            new DrawEntry([
                'position' => 7,
            ]);

        $meetings =
            collect([
                new Meeting([
                    'number' => 1,
                ]),

                new Meeting([
                    'number' => 2,
                ]),

                new Meeting([
                    'number' => 3,
                ]),
            ])->keyBy('number');

        $meeting =
            app(
                DrawCalendar::class,
            )->meetingForEntry(
                session: $session,
                entry: $entry,
                meetings: $meetings,
            );

        expect(
            $meeting,
        )->toBeNull();
    },
);

it(
    'returns the expected positions for a meeting',
    function (): void {
        $session =
            new Session([
                'beneficiaries_per_meeting' => 3,
            ]);

        $meeting =
            new Meeting([
                'number' => 2,
            ]);

        $positions =
            app(
                DrawCalendar::class,
            )->positionsForMeeting(
                session: $session,
                meeting: $meeting,
            );

        expect(
            $positions,
        )->toBe([
            4,
            5,
            6,
        ]);
    },
);

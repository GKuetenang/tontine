<?php

namespace App\Data;

use App\Models\Draw;
use App\Models\DrawEntry;
use App\Support\DrawCalendar;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Draw')]
class DrawData extends Data
{
    public function __construct(
        public int $id,

        public Optional|array $entries,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public ?CarbonImmutable $confirmed_at,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $created_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        Draw $draw,
    ): self {
        $session =
            $draw->session;

        $meetings =
            $session
                ->meetings
                ->keyBy('number');

        $calendar =
            app(DrawCalendar::class);

        $entries =
            $draw->relationLoaded('entries')
            ? $draw
                ->entries
                ->sortBy('position')
                ->values()
                ->map(
                    function (
                        DrawEntry $entry,
                    ) use (
                        $session,
                        $meetings,
                        $calendar,
                    ): DrawEntryData {
                        $expectedMeeting =
                            $calendar
                                ->meetingForEntry(
                                    session: $session,

                                    entry: $entry,

                                    meetings: $meetings,
                                );

                        return DrawEntryData::fromModel(
                            entry: $entry,

                            expectedMeeting: $expectedMeeting
                                ? ExpectedDrawMeetingData::fromModel(
                                    $expectedMeeting,
                                )
                                : null,
                        );
                    },
                )
                ->all()
            : Optional::create();

        return new self(
            id: $draw->id,

            entries: $entries,

            confirmed_at: $draw->confirmed_at,

            created_at: $draw->created_at,

            updated_at: $draw->updated_at,
        );
    }
}

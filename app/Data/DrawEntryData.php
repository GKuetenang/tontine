<?php

namespace App\Data;

use App\Models\DrawEntry;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'DrawEntry')]
class DrawEntryData extends Data
{
    public function __construct(
        public int $id,
        public int $position,
        public int $entry_number,

        public Optional|SessionParticipantData $session_participant,

        public ?ExpectedDrawMeetingData $expected_meeting,

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
        DrawEntry $entry,
        ?ExpectedDrawMeetingData $expectedMeeting = null,
    ): self {
        return new self(
            id: $entry->id,

            position: $entry->position,

            entry_number: $entry->entry_number,

            session_participant: $entry->relationLoaded(
                'sessionParticipant',
            )
                ? SessionParticipantData::fromModel(
                    $entry->sessionParticipant,
                )
                : Optional::create(),

            expected_meeting: $expectedMeeting,

            created_at: $entry->created_at,

            updated_at: $entry->updated_at,
        );
    }
}

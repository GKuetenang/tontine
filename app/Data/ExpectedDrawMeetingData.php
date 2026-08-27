<?php

namespace App\Data;

use App\Models\Meeting;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'ExpectedDrawMeeting')]
class ExpectedDrawMeetingData extends Data
{
    public function __construct(
        public int $id,
        public int $number,
        public string $slug,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $scheduled_at,
    ) {}

    public static function fromModel(
        Meeting $meeting,
    ): self {
        return new self(
            id: $meeting->id,
            number: $meeting->number,
            slug: $meeting->slug,
            scheduled_at: $meeting->scheduled_at,
        );
    }
}

<?php

namespace App\Data;

use App\Models\Meeting;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MeetingSummaryData extends Data
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
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
            slug: $meeting->slug,
            name: $meeting->name,
            scheduled_at: $meeting->scheduled_at,
        );
    }
}

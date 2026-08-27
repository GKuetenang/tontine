<?php

namespace App\Data;

use App\Models\MeetingAgendaItem;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingAgendaItem')]
class MeetingAgendaItemData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public int $position,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        MeetingAgendaItem $agendaItem,
    ): self {
        return new self(
            id: $agendaItem->id,
            title: $agendaItem->title,
            description: $agendaItem->description,
            position: $agendaItem->position,
            created_at: $agendaItem->created_at,
            updated_at: $agendaItem->updated_at,
        );
    }
}

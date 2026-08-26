<?php

namespace App\Data;

use App\Models\MeetingDecision;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingDecision')]
class MeetingDecisionData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,

        public Optional|MeetingAgendaItemData|null $agenda_item,

        public Optional|MemberUserData|null $creator,

        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        MeetingDecision $decision,
    ): self {
        return new self(
            id: $decision->id,

            title: $decision->title,

            description: $decision->description,

            agenda_item: $decision->relationLoaded(
                'agendaItem',
            )
                ? (
                    $decision->agendaItem
                    ? MeetingAgendaItemData::fromModel(
                        $decision->agendaItem,
                    )
                    : null
                )
                : Optional::create(),

            creator: $decision->relationLoaded(
                'creator',
            )
                ? (
                    $decision->creator
                    ? MemberUserData::from(
                        $decision->creator,
                    )
                    : null
                )
                : Optional::create(),

            created_at: $decision->created_at,

            updated_at: $decision->updated_at,
        );
    }
}

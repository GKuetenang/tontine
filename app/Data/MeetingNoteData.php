<?php

namespace App\Data;

use App\Models\MeetingNote;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingNote')]
class MeetingNoteData extends Data
{
    public function __construct(
        public int $id,
        public string $content,

        public Optional|MeetingAgendaItemData|null $agenda_item,
        public Optional|MemberUserData|null $creator,

        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        MeetingNote $note,
    ): self {
        return new self(
            id: $note->id,
            content: $note->content,

            agenda_item: $note->relationLoaded('agendaItem')
                ? (
                    $note->agendaItem
                    ? MeetingAgendaItemData::fromModel(
                        $note->agendaItem,
                    )
                    : null
                )
                : Optional::create(),

            creator: $note->relationLoaded('creator')
                ? (
                    $note->creator
                    ? MemberUserData::from(
                        $note->creator,
                    )
                    : null
                )
                : Optional::create(),

            created_at: $note->created_at,
            updated_at: $note->updated_at,
        );
    }
}

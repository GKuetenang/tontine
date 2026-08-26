<?php

namespace App\Data;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Meeting')]
class MeetingData extends Data
{
    public function __construct(
        public int $id,
        public int $number,
        public string $title,
        public string $slug,
        public ?string $description,
        public ?string $location,
        public MeetingStatus $status,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $scheduled_at,
        public Optional|array $agenda_items,

        public Optional|CarbonImmutable|null $opened_at,
        public Optional|CarbonImmutable|null $closed_at,

        public Optional|int $attendances_count,
        public Optional|int $contributions_count,
        public Optional|array $attendances,
        public Optional|array $contributions,
        public Optional|array $notes,

        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(Meeting $meeting): self
    {
        return new self(
            id: $meeting->id,
            number: $meeting->number,
            title: $meeting->title,
            slug: $meeting->slug,
            description: $meeting->description,
            location: $meeting->location,
            status: $meeting->status,
            scheduled_at: $meeting->scheduled_at,
            opened_at: $meeting->opened_at,
            closed_at: $meeting->closed_at,

            attendances_count: array_key_exists(
                'attendances_count',
                $meeting->getAttributes(),
            )
                ? (int) $meeting->attendances_count
                : Optional::create(),

            contributions_count: array_key_exists(
                'contributions_count',
                $meeting->getAttributes(),
            )
                ? (int) $meeting->contributions_count
                : Optional::create(),

            agenda_items: $meeting->relationLoaded('agendaItems')
                ? MeetingAgendaItemData::collect(
                    $meeting->agendaItems,
                )->all()
                : Optional::create(),
            attendances: $meeting->relationLoaded('attendances')
                ? MeetingAttendanceData::collect(
                    $meeting->attendances,
                )->all()
                : Optional::create(),
            contributions: $meeting->relationLoaded('contributions')
                ? ContributionData::collect(
                    $meeting->contributions,
                )->all()
                : Optional::create(),
            notes: $meeting->relationLoaded('notes')
                ? MeetingNoteData::collect(
                    $meeting->notes,
                )->all()
                : Optional::create(),

            created_at: $meeting->created_at,
            updated_at: $meeting->updated_at,
        );
    }
}

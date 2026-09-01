<?php

namespace App\Data;

use App\Enums\MeetingRecurrence;
use App\Models\MeetingSchedule;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingSchedule')]
class MeetingScheduleData extends Data
{
    public function __construct(
        public int $id,
        public MeetingRecurrence $recurrence,
        public string $rrule,
        public string $timezone,
        public string $default_title,
        public ?string $default_location,
        public int $default_duration_minutes,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public CarbonImmutable $starts_at,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public ?CarbonImmutable $generated_at,
    ) {}

    public static function fromModel(MeetingSchedule $schedule): self
    {
        return new self(
            id: $schedule->id,
            recurrence: MeetingRecurrence::fromRRule($schedule->rrule),
            rrule: $schedule->rrule,
            timezone: $schedule->timezone,
            default_title: $schedule->default_title,
            default_location: $schedule->default_location,
            default_duration_minutes: $schedule->default_duration_minutes,
            starts_at: $schedule->starts_at,
            generated_at: $schedule->generated_at,
        );
    }
}

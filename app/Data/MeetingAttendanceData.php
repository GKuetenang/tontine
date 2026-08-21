<?php

namespace App\Data;

use App\Enums\AttendanceStatus;
use App\Models\MeetingAttendance;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'MeetingAttendance')]
class MeetingAttendanceData extends Data
{
    public function __construct(
        public int $id,
        public AttendanceStatus $status,
        public ?CarbonImmutable $checked_in_at,
        public ?string $note,

        public Optional|SessionParticipantData $session_participant,

        public CarbonImmutable $created_at,
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        MeetingAttendance $attendance,
    ): self {
        return new self(
            id: $attendance->id,
            status: $attendance->status,
            checked_in_at: $attendance->checked_in_at,
            note: $attendance->note,

            session_participant: $attendance->relationLoaded('sessionParticipant')
                ? SessionParticipantData::fromModel(
                    $attendance->sessionParticipant,
                )
                : Optional::create(),

            created_at: $attendance->created_at,
            updated_at: $attendance->updated_at,
        );
    }
}

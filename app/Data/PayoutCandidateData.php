<?php

namespace App\Data;

use App\Models\DrawEntry;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'PayoutCandidate')]
class PayoutCandidateData extends Data
{
    public function __construct(
        public int $draw_entry_id,
        public int $position,
        public int $entry_number,
        public int $session_participant_id,
        public string $member_name,
        public bool $expected,
    ) {}

    public static function fromModel(
        DrawEntry $entry,
        bool $expected = false,
    ): self {
        return new self(
            draw_entry_id: $entry->id,

            position: $entry->position,

            entry_number: $entry->entry_number,

            session_participant_id: $entry->session_participant_id,

            member_name: $entry
                ->sessionParticipant
                ->membership
                ->user
                ->full_name,

            expected: $expected,
        );
    }
}

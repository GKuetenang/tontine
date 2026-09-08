<?php

namespace App\Data;

use App\Models\Penalty;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Penalty')]
class PenaltyData extends Data
{
    public function __construct(
        public int $id,
        public string $member_name,
        public string $meeting_name,
        public string $rule_name,
        public string $trigger_label,
        public string $amount,
        public string $source,
        public string $source_label,
        public string $status,
        public string $status_label,
        public ?string $reason,
        public ?string $waiver_reason,
        public string $assessed_at,
    ) {}

    public static function fromModel(Penalty $penalty): self
    {
        return new self(
            id: $penalty->id,
            member_name: $penalty->membership->user->full_name,
            meeting_name: $penalty->meeting->title,
            rule_name: $penalty->rule_name,
            trigger_label: $penalty->trigger->label(),
            amount: $penalty->amount,
            source: $penalty->source,
            source_label: $penalty->source === 'automatic' ? __('Automatique') : __('Manuelle'),
            status: $penalty->status->value,
            status_label: $penalty->status->label(),
            reason: $penalty->reason,
            waiver_reason: $penalty->waiver_reason,
            assessed_at: $penalty->assessed_at->format('Y-m-d\TH:i:s'),
        );
    }
}

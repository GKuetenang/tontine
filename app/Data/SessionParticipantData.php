<?php

namespace App\Data;

use App\Models\SessionParticipant;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'SessionParticipant')]
class SessionParticipantData extends Data
{
    public function __construct(
        public int $id,

        public int $contribution_amount,

        public int $draw_entries_count,

        public bool $is_active,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable|null $joined_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable|null $left_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $created_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public Optional|CarbonImmutable $updated_at,

        public Optional|MembershipData $membership,
    ) {}

    public static function fromModel(
        SessionParticipant $participant,
    ): self {
        return new self(
            id: $participant->id,

            contribution_amount: $participant->contribution_amount,

            draw_entries_count: $participant->draw_entries_count,

            is_active: $participant->is_active,

            joined_at: $participant->joined_at,

            left_at: $participant->left_at,

            created_at: $participant->created_at,

            updated_at: $participant->updated_at,

            membership: $participant->relationLoaded('membership')
                ? MembershipData::from(
                    $participant->membership
                )
                : Optional::create(),
        );
    }
}

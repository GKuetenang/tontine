<?php

namespace App\Data;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Payout')]
class PayoutData extends Data
{
    public function __construct(
        public int $id,
        public int $amount,
        public PayoutStatus $status,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public ?CarbonImmutable $paid_at,

        public Optional|DrawEntryData $draw_entry,

        public Optional|MemberUserData|null $creator,

        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $created_at,
        #[WithTransformer(
            DateTimeInterfaceTransformer::class,
            format: 'Y-m-d\TH:i:s',
        )]
        public CarbonImmutable $updated_at,
    ) {}

    public static function fromModel(
        Payout $payout,
    ): self {
        return new self(
            id: $payout->id,

            amount: $payout->amount,

            status: $payout->status,

            paid_at: $payout->paid_at,

            draw_entry: $payout->relationLoaded(
                'drawEntry',
            )
                ? DrawEntryData::from(
                    $payout->drawEntry,
                )
                : Optional::create(),

            creator: $payout->relationLoaded(
                'creator',
            )
                ? (
                    $payout->creator
                    ? MemberUserData::from(
                        $payout->creator,
                    )
                    : null
                )
                : Optional::create(),

            created_at: $payout->created_at,

            updated_at: $payout->updated_at,
        );
    }
}

<?php

namespace App\Data;

use App\Enums\DonationStatus;
use App\Models\Donation;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Donation')]
class DonationData extends Data
{
    public function __construct(
        public int $id,
        public string $amount,
        public string $reason,
        public DonationStatus $status,
        public string $member_name,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public ?CarbonImmutable $paid_at,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public CarbonImmutable $created_at,
    ) {}

    public static function fromModel(Donation $donation): self
    {
        return new self(
            id: $donation->id,
            amount: $donation->amount,
            reason: $donation->reason,
            status: $donation->status,
            member_name: $donation->membership->user->full_name,
            paid_at: $donation->paid_at,
            created_at: $donation->created_at,
        );
    }
}

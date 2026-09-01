<?php

namespace App\Data;

use App\Models\InsuranceContribution;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'InsuranceContribution')]
class InsuranceContributionData extends Data
{
    public function __construct(
        public int $id,
        public string $amount,
        public ?string $description,
        public string $member_name,
        public ?string $creator_name,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public CarbonImmutable $occurred_at,
    ) {}

    public static function fromModel(InsuranceContribution $contribution): self
    {
        return new self(
            id: $contribution->id,
            amount: $contribution->amount,
            description: $contribution->description,
            member_name: $contribution->membership->user->full_name,
            creator_name: $contribution->creator?->full_name,
            occurred_at: $contribution->occurred_at,
        );
    }
}

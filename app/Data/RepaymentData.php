<?php

namespace App\Data;

use App\Models\Repayment;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Repayment')]
class RepaymentData extends Data
{
    public function __construct(
        public int $id,
        public int $loan_id,
        public string $member_name,
        public string $amount,
        public string $interest_amount,
        public string $principal_amount,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public CarbonImmutable $paid_at,
    ) {}

    public static function fromModel(Repayment $repayment): self
    {
        return new self($repayment->id, $repayment->loan_id, $repayment->loan->membership->user->name, $repayment->amount, $repayment->interest_amount, $repayment->principal_amount, $repayment->paid_at);
    }
}

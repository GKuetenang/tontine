<?php

namespace App\Data;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Loan')]
class LoanData extends Data
{
    public function __construct(
        public int $id,
        public string $member_name,
        public string $principal_amount,
        public string $interest_rate,
        public int $term_months,
        public string $interest_amount,
        public string $total_due,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
        public CarbonImmutable $due_at,
        public ?string $reason,
        public LoanStatus $status,
    ) {}

    public static function fromModel(Loan $loan): self
    {
        return new self($loan->id, $loan->membership->user->name, $loan->principal_amount, $loan->interest_rate, $loan->term_months, $loan->interest_amount, $loan->total_due, $loan->due_at, $loan->reason, $loan->status);
    }
}

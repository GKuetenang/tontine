<?php

namespace App\Data;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Transaction')]
class TransactionData extends Data
{
    public function __construct(
        public int $id,
        public TransactionType $type,
        public TransactionDirection $direction,
        public string $amount,
        public ?string $description,
        public ?string $member_name,
        public ?string $creator_name,
        public ?string $source_type,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s')]
        public CarbonImmutable $occurred_at,
    ) {}

    public static function fromModel(Transaction $transaction): self
    {
        return new self(
            id: $transaction->id,
            type: $transaction->type,
            direction: $transaction->direction,
            amount: $transaction->amount,
            description: $transaction->description,
            member_name: $transaction->membership?->user?->name,
            creator_name: $transaction->creator?->name,
            source_type: $transaction->transactionable_type
                ? class_basename($transaction->transactionable_type)
                : null,
            occurred_at: $transaction->occurred_at,
        );
    }
}

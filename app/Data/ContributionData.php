<?php

namespace App\Data;

use App\Enums\ContributionStatus;
use App\Enums\TransactionDirection;
use App\Models\Contribution;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'Contribution')]
class ContributionData extends Data
{
    public function __construct(
        public int $id,
        public int $amount_due,
        public int $amount_paid,
        public int $remaining_amount,
        public ContributionStatus $status,

        public Optional|SessionParticipantData $session_participant,

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
        Contribution $contribution,
    ): self {
        $amountPaid = $contribution
            ->relationLoaded('transactions')
            ? (int) $contribution
                ->transactions
                ->where(
                    'direction',
                    TransactionDirection::Credit,
                )
                ->sum('amount')
            : $contribution->amountPaid();

        $remainingAmount = max(
            0,
            $contribution->amount_due - $amountPaid,
        );

        $status = match (true) {
            $amountPaid === 0 =>
            ContributionStatus::Unpaid,

            $amountPaid < $contribution->amount_due =>
            ContributionStatus::Partial,

            default =>
            ContributionStatus::Paid,
        };

        return new self(
            id: $contribution->id,
            amount_due: $contribution->amount_due,
            amount_paid: $amountPaid,
            remaining_amount: $remainingAmount,
            status: $status,

            session_participant: $contribution->relationLoaded(
                'sessionParticipant',
            )
                ? SessionParticipantData::fromModel(
                    $contribution->sessionParticipant,
                )
                : Optional::create(),

            created_at: $contribution->created_at,
            updated_at: $contribution->updated_at,
        );
    }
}

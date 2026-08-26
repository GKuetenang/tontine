<?php

namespace App\Actions\Contributions;

use App\Enums\MeetingStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Contribution;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecordContributionPaymentAction
{
    public function execute(
        Contribution $contribution,
        User $creator,
        int $amount,
        CarbonImmutable $occurredAt,
        ?string $description = null,
    ): Transaction {
        return DB::transaction(
            function () use (
                $contribution,
                $creator,
                $amount,
                $occurredAt,
                $description,
            ): Transaction {
                if ($amount < 1) {
                    throw ValidationException::withMessages([
                        'amount' => __(
                            'Le montant doit être supérieur à zéro.'
                        ),
                    ]);
                }

                if (! in_array(
                    $contribution->meeting->status,
                    [
                        MeetingStatus::InProgress,
                        MeetingStatus::Completed,
                    ],
                    true,
                )) {
                    throw ValidationException::withMessages([
                        'contribution' => __(
                            'Cette cotisation ne peut pas encore recevoir de paiement.'
                        ),
                    ]);
                }
                $remainingAmount =
                    $contribution->remainingAmount();

                if ($amount > $remainingAmount) {
                    throw ValidationException::withMessages([
                        'amount' => __(
                            'Le montant payé dépasse le montant restant dû.'
                        ),
                    ]);
                }

                $participant =
                    $contribution->sessionParticipant;

                $meeting =
                    $contribution->meeting;

                $transaction = new Transaction();

                $transaction->fill([
                    'type' => TransactionType::Contribution,
                    'direction' => TransactionDirection::Credit,
                    'amount' => $amount,
                    'occurred_at' => $occurredAt,
                    'description' => $description,
                ]);

                $transaction->session()
                    ->associate($meeting->session);

                $transaction->membership()
                    ->associate($participant->membership);

                $transaction->creator()
                    ->associate($creator);

                $contribution
                    ->transactions()
                    ->save($transaction);

                return $transaction->refresh();
            },
        );
    }
}

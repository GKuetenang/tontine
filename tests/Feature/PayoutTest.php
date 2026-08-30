<?php

use App\Actions\Payouts\PayPayoutAction;
use App\Enums\PayoutStatus;
use App\Enums\TransactionDirection;
use App\Models\Draw;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * @return array{
 *     user: User,
 *     session: Session,
 *     participant: SessionParticipant,
 *     payout: Payout
 * }
 */
function payoutTestContext(): array
{
    $user =
        User::factory()->create();

    $session =
        Session::factory()->create([
            'status' => 'active',
        ]);

    $meeting =
        Meeting::factory()
            ->for($session)
            ->create();

    $participant =
        SessionParticipant::factory()
            ->for($session)
            ->create();

    $draw =
        Draw::factory()
            ->for($session)
            ->create([
                'confirmed_at' => now(),
            ]);

    $entry =
        DrawEntry::factory()
            ->for($draw)
            ->for(
                $participant,
                'sessionParticipant',
            )
            ->create([
                'position' => 1,
                'entry_number' => 1,
            ]);

    $payout =
        Payout::factory()
            ->for($meeting)
            ->for(
                $entry,
                'drawEntry',
            )
            ->create([
                'status' => PayoutStatus::Pending,

                'amount' => '500000.00',

                'paid_at' => null,
            ]);

    return compact(
        'user',
        'session',
        'participant',
        'payout',
    );
}

it(
    'pays a pending payout',
    function (): void {
        ['user' => $user, 'payout' => $payout] =
            payoutTestContext();

        $result =
            app(
                PayPayoutAction::class,
            )->execute(
                payout: $payout,

                user: $user,
            );

        expect(
            $result->status,
        )->toBe(
            PayoutStatus::Paid,
        );

        expect(
            $result->paid_at,
        )->not->toBeNull();
    },
);

it(
    'creates exactly one outgoing transaction when a payout is paid',
    function (): void {
        ['user' => $user, 'payout' => $payout] =
            payoutTestContext();

        app(
            PayPayoutAction::class,
        )->execute(
            payout: $payout,

            user: $user,
        );

        expect(
            Transaction::query()
                ->count('*'),
        )->toBe(1);

        $transaction =
            Transaction::query()
                ->firstOrFail();

        expect(
            $transaction->amount,
        )->toBe(
            '500000.00',
        );

        expect(
            $transaction->direction,
        )->toBe(
            TransactionDirection::Debit,
        );

        expect(
            $transaction->transactionable_id,
        )->toBe(
            $payout->id,
        );

        expect(
            $transaction->transactionable_type,
        )->toBe(
            Payout::class,
        );

        expect(
            $transaction->created_by,
        )->toBe(
            $user->id,
        );
    },
);

it(
    'preserves two decimal places in the payout transaction amount',
    function (): void {
        ['user' => $user, 'payout' => $payout] =
            payoutTestContext();

        $payout->update([
            'amount' => '1250.50',
        ]);

        app(
            PayPayoutAction::class,
        )->execute(
            payout: $payout,

            user: $user,
        );

        expect(
            Transaction::query()
                ->firstOrFail()
                ->amount,
        )->toBe(
            '1250.50',
        );
    },
);

it(
    'associates the payout transaction with the beneficiary membership',
    function (): void {
        ['user' => $user, 'participant' => $participant, 'payout' => $payout] =
            payoutTestContext();

        app(
            PayPayoutAction::class,
        )->execute(
            payout: $payout,

            user: $user,
        );

        $transaction =
            Transaction::query()
                ->firstOrFail();

        expect(
            $transaction->membership_id,
        )->toBe(
            $participant
                ->membership_id,
        );
    },
);

it(
    'associates the transaction with the payout session',
    function (): void {
        ['user' => $user, 'session' => $session, 'payout' => $payout] =
            payoutTestContext();

        app(
            PayPayoutAction::class,
        )->execute(
            payout: $payout,

            user: $user,
        );

        $transaction =
            Transaction::query()
                ->firstOrFail();

        expect(
            $transaction->session_id,
        )->toBe(
            $session->id,
        );
    },
);

it(
    'does not pay the same payout twice',
    function (): void {
        ['user' => $user, 'payout' => $payout] =
            payoutTestContext();

        $action =
            app(
                PayPayoutAction::class,
            );

        $action->execute(
            payout: $payout,

            user: $user,
        );

        expect(
            fn () => $action->execute(
                payout: $payout,

                user: $user,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            Transaction::query()
                ->count('*'),
        )->toBe(1);
    },
);

it(
    'does not pay a cancelled payout',
    function (): void {
        ['user' => $user, 'payout' => $payout] =
            payoutTestContext();

        $payout->update([
            'status' => PayoutStatus::Cancelled,
        ]);

        expect(
            fn () => app(
                PayPayoutAction::class,
            )->execute(
                payout: $payout,

                user: $user,
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            Transaction::query()
                ->count('*'),
        )->toBe(0);
    },
);

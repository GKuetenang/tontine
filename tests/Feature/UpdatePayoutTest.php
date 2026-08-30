<?php

use App\Actions\Payouts\UpdatePayoutAction;
use App\Models\Payout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it(
    'updates the amount of a pending payout',
    function (): void {
        $payout =
            Payout::factory()
                ->pending()
                ->create([
                    'amount' => '500000.00',
                ]);

        $updated =
            app(
                UpdatePayoutAction::class,
            )->execute(
                payout: $payout,

                amount: '525000.50',
            );

        expect(
            $updated->amount,
        )->toBe(
            '525000.50',
        );
    },
);

it(
    'does not allow a paid payout to be modified',
    function (): void {
        $payout =
            Payout::factory()
                ->paid()
                ->create();

        expect(
            fn () => app(
                UpdatePayoutAction::class,
            )->execute(
                payout: $payout,

                amount: '600000.00',
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'does not allow a cancelled payout to be modified',
    function (): void {
        $payout =
            Payout::factory()
                ->cancelled()
                ->create();

        expect(
            fn () => app(
                UpdatePayoutAction::class,
            )->execute(
                payout: $payout,

                amount: '600000.00',
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

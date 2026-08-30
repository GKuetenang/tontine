<?php

use App\Actions\Payouts\CancelPayoutAction;
use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it(
    'cancels a pending payout',
    function (): void {
        $payout =
            Payout::factory()
                ->pending()
                ->create();

        $cancelled =
            app(
                CancelPayoutAction::class,
            )->execute(
                $payout,
            );

        expect(
            $cancelled->status,
        )->toBe(
            PayoutStatus::Cancelled,
        );
    },
);

it(
    'does not allow a paid payout to be cancelled',
    function (): void {
        $payout =
            Payout::factory()
                ->paid()
                ->create();

        expect(
            fn () => app(
                CancelPayoutAction::class,
            )->execute(
                $payout,
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'keeps an already cancelled payout cancelled',
    function (): void {
        $payout =
            Payout::factory()
                ->cancelled()
                ->create();

        $result =
            app(
                CancelPayoutAction::class,
            )->execute(
                $payout,
            );

        expect(
            $result->status,
        )->toBe(
            PayoutStatus::Cancelled,
        );
    },
);

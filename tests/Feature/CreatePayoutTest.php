<?php

use App\Actions\Payouts\CreatePayoutAction;
use App\Enums\MeetingStatus;
use App\Enums\PayoutStatus;
use App\Models\Draw;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user =
        User::factory()->create();

    $this->session =
        Session::factory()->create([
            'status' => 'active',
        ]);

    $this->meeting =
        Meeting::factory()
            ->for($this->session)
            ->create([
                'status' => MeetingStatus::InProgress,
            ]);

    $this->draw =
        Draw::factory()
            ->for($this->session)
            ->create([
                'confirmed_at' => now(),
            ]);

    $this->participant =
        SessionParticipant::factory()
            ->for($this->session)
            ->create();

    $this->drawEntry =
        DrawEntry::factory()
            ->for($this->draw)
            ->for(
                $this->participant,
                'sessionParticipant',
            )
            ->create([
                'position' => 1,
                'entry_number' => 1,
            ]);
});

it(
    'creates a payout for a confirmed draw entry',
    function (): void {
        $payout =
            app(
                CreatePayoutAction::class,
            )->execute(
                meeting: $this->meeting,

                drawEntry: $this->drawEntry,

                creator: $this->user,

                amount: '500000.00',
            );

        expect($payout)
            ->toBeInstanceOf(
                Payout::class,
            )
            ->and($payout->status)
            ->toBe(
                PayoutStatus::Pending,
            )
            ->and($payout->amount)
            ->toBe('500000.00')
            ->and($payout->meeting_id)
            ->toBe(
                $this->meeting->id,
            )
            ->and($payout->draw_entry_id)
            ->toBe(
                $this->drawEntry->id,
            )
            ->and($payout->created_by)
            ->toBe(
                $this->user->id,
            )
            ->and($payout->paid_at)
            ->toBeNull();

        $this->assertDatabaseHas(
            'payouts',
            [
                'meeting_id' => $this->meeting->id,

                'draw_entry_id' => $this->drawEntry->id,

                'amount' => '500000.00',

                'status' => PayoutStatus::Pending
                    ->value,

                'created_by' => $this->user->id,
            ],
        );
    },
);

it(
    'preserves a payout amount with two decimal places',
    function (): void {
        $payout =
            app(
                CreatePayoutAction::class,
            )->execute(
                meeting: $this->meeting,

                drawEntry: $this->drawEntry,

                creator: $this->user,

                amount: '1250.50',
            );

        expect(
            $payout->amount,
        )->toBe(
            '1250.50',
        );
    },
);

it(
    'does not allow a payout from an unconfirmed draw',
    function (): void {
        $this->draw->confirmed_at = null;
        $this->draw->save();

        expect(
            fn () => app(
                CreatePayoutAction::class,
            )->execute(
                meeting: $this->meeting,

                drawEntry: $this->drawEntry,

                creator: $this->user,

                amount: '500000.00',
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            Payout::query()->count(),
        )->toBe(0);
    },
);

it(
    'does not allow a draw entry from another session',
    function (): void {
        $otherSession =
            Session::factory()
                ->create([
                    'status' => 'active',
                ]);

        $otherDraw =
            Draw::factory()
                ->for($otherSession)
                ->create([
                    'confirmed_at' => now(),
                ]);

        $otherParticipant =
            SessionParticipant::factory()
                ->for($otherSession)
                ->create();

        $otherEntry =
            DrawEntry::factory()
                ->for($otherDraw)
                ->for(
                    $otherParticipant,
                    'sessionParticipant',
                )
                ->create([
                    'position' => 1,
                ]);

        expect(
            fn () => app(
                CreatePayoutAction::class,
            )->execute(
                meeting: $this->meeting,

                drawEntry: $otherEntry,

                creator: $this->user,

                amount: '500000.00',
            ),
        )->toThrow(
            ValidationException::class,
        );
    },
);

it(
    'does not allow two payouts for the same draw entry',
    function (): void {
        $action =
            app(
                CreatePayoutAction::class,
            );

        $action->execute(
            meeting: $this->meeting,

            drawEntry: $this->drawEntry,

            creator: $this->user,

            amount: '500000.00',
        );

        expect(
            fn () => $action->execute(
                meeting: $this->meeting,

                drawEntry: $this->drawEntry,

                creator: $this->user,

                amount: '500000.00',
            ),
        )->toThrow(
            ValidationException::class,
        );

        expect(
            Payout::query()->count(),
        )->toBe(1);
    },
);

it(
    'allows several payouts for the same meeting',
    function (): void {
        $secondParticipant =
            SessionParticipant::factory()
                ->for($this->session)
                ->create();

        $secondEntry =
            DrawEntry::factory()
                ->for($this->draw)
                ->for(
                    $secondParticipant,
                    'sessionParticipant',
                )
                ->create([
                    'position' => 2,
                ]);

        $action =
            app(
                CreatePayoutAction::class,
            );

        $action->execute(
            meeting: $this->meeting,

            drawEntry: $this->drawEntry,

            creator: $this->user,

            amount: '500000.00',
        );

        $action->execute(
            meeting: $this->meeting,

            drawEntry: $secondEntry,

            creator: $this->user,

            amount: '500000.00',
        );

        expect(
            $this->meeting
                ->payouts()
                ->count(),
        )->toBe(2);
    },
);

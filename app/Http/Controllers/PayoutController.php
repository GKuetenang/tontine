<?php

namespace App\Http\Controllers;

use App\Actions\Payouts\CancelPayoutAction;
use App\Actions\Payouts\CreatePayoutAction;
use App\Actions\Payouts\PayPayoutAction;
use App\Actions\Payouts\UpdatePayoutAction;
use App\Http\Requests\StorePayoutRequest;
use App\Http\Requests\UpdatePayoutRequest;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function store(
        StorePayoutRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        CreatePayoutAction $action,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [
                Payout::class,
                $meeting,
            ],
        );

        $drawEntry =
            DrawEntry::query()
                ->whereHas(
                    'draw',
                    fn ($query) => $query->where(
                        'session_id',
                        $session->id,
                    ),
                )
                ->findOrFail(
                    $request->integer(
                        'draw_entry_id',
                    ),
                );

        $action->execute(
            meeting: $meeting,

            drawEntry: $drawEntry,

            creator: $request->user(),

            amount: $request->string(
                'amount',
            )->toString(),
        );

        return Inertia::flash(
            'success',
            __(
                'Le versement a été créé avec succès.'
            ),
        )->back();
    }

    public function update(
        UpdatePayoutRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        Payout $payout,
        UpdatePayoutAction $action,
    ): RedirectResponse {
        abort_unless(
            $payout->meeting_id
                === $meeting->id,
            404,
        );

        $this->authorize(
            'update',
            $payout,
        );

        $action->execute(
            payout: $payout,

            amount: $request->string(
                'amount',
            )->toString(),
        );

        return Inertia::flash(
            'success',
            __(
                'Le versement a été modifié avec succès.'
            ),
        )->back();
    }

    public function pay(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        Payout $payout,
        PayPayoutAction $action,
    ): RedirectResponse {
        abort_unless(
            $payout->meeting_id
                === $meeting->id,
            404,
        );

        $this->authorize(
            'pay',
            $payout,
        );

        $action->execute(
            payout: $payout,

            user: request()->user(),
        );

        return Inertia::flash(
            'success',
            __(
                'Le versement a été effectué avec succès.'
            ),
        )->back();
    }

    public function cancel(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        Payout $payout,
        CancelPayoutAction $action,
    ): RedirectResponse {
        abort_unless(
            $payout->meeting_id
                === $meeting->id,
            404,
        );

        $this->authorize(
            'cancel',
            $payout,
        );

        $action->execute(
            $payout,
        );

        return Inertia::flash(
            'success',
            __(
                'Le versement a été annulé.'
            ),
        )->back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Contributions\RecordContributionPaymentAction;
use App\Http\Requests\StoreContributionPaymentRequest;
use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Tontine;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ContributionPaymentController extends Controller
{
    public function store(
        StoreContributionPaymentRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        Contribution $contribution,
        RecordContributionPaymentAction $action,
    ): RedirectResponse {
        abort_unless(
            $contribution->meeting_id === $meeting->id,
            404,
        );

        $this->authorize(
            'pay',
            $contribution,
        );

        $action->execute(
            contribution: $contribution,
            creator: $request->user(),
            amount: $request->integer('amount'),
            occurredAt: CarbonImmutable::parse(
                $request->string('occurred_at'),
            ),
            description: $request->input(
                'description',
            ),
        );

        return Inertia::flash(
            'success',
            __(
                'Le paiement de la cotisation a été enregistré avec succès.'
            ),
        )->back();
    }
}

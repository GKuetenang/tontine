<?php

namespace App\Http\Controllers;

use App\Actions\Donations\CancelDonationAction;
use App\Actions\Donations\CreateDonationAction;
use App\Actions\Donations\PayDonationAction;
use App\Data\DonationData;
use App\Data\SessionData;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends WithUserSearchController
{
    public function index(Tontine $tontine, Session $session): Response
    {
        $this->authorize('viewAny', [Donation::class, $session]);

        $donations = $session->donations()
            ->with('membership.user')
            ->latest()
            ->paginate();

        return Inertia::render('donations/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug],
            'session' => SessionData::fromModel($session),
            'collection' => DonationData::collect($donations),
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
        ]);
    }

    public function store(StoreDonationRequest $request, Tontine $tontine, Session $session, CreateDonationAction $action): RedirectResponse
    {
        $this->authorize('create', [Donation::class, $session]);

        $membership = $session->participants()
            ->active()
            ->where('membership_id', $request->integer('membership_id'))
            ->firstOrFail()
            ->membership;

        $action->execute(
            session: $session,
            membership: $membership,
            creator: $request->user(),
            amount: $request->string('amount')->toString(),
            reason: $request->string('reason')->toString(),
        );

        return Inertia::flash('success', __('Le don a été créé avec succès.'))->back();
    }

    public function pay(Tontine $tontine, Session $session, Donation $donation, PayDonationAction $action): RedirectResponse
    {
        abort_unless($donation->session_id === $session->id, 404);
        $this->authorize('pay', $donation);

        $action->execute($donation, request()->user());

        return Inertia::flash('success', __('Le don a été effectué avec succès.'))->back();
    }

    public function cancel(Tontine $tontine, Session $session, Donation $donation, CancelDonationAction $action): RedirectResponse
    {
        abort_unless($donation->session_id === $session->id, 404);
        $this->authorize('cancel', $donation);

        $action->execute($donation);

        return Inertia::flash('success', __('Le don a été annulé.'))->back();
    }
}

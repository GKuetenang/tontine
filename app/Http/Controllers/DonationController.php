<?php

namespace App\Http\Controllers;

use App\Actions\Donations\CancelDonationAction;
use App\Actions\Donations\CreateDonationAction;
use App\Actions\Donations\PayDonationAction;
use App\Data\DonationData;
use App\Data\SessionData;
use App\Enums\DonationStatus;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Models\Group;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends WithUserSearchController
{
    public function index(Request $request, Group $group, Session $session): Response
    {
        $this->authorize('viewAny', [Donation::class, $session]);

        $q = $request->string('q')->trim()->toString();
        $donations = $session->donations()
            ->with('membership.user')
            ->when($q, fn ($query) => $query->whereHas('membership.user', fn ($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")))
            ->orderFromRequest($request)
            ->paginate()
            ->withQueryString();

        return Inertia::render('donations/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'session' => SessionData::fromModel($session),
            'collection' => DonationData::collect($donations),
            'q' => $q ?: null,
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
            'statuses' => DonationStatus::getOptions(),
        ]);
    }

    public function store(StoreDonationRequest $request, Group $group, Session $session, CreateDonationAction $action): RedirectResponse
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

    public function pay(Group $group, Session $session, Donation $donation, PayDonationAction $action): RedirectResponse
    {
        abort_unless($donation->session_id === $session->id, 404);
        $this->authorize('pay', $donation);

        $action->execute($donation, request()->user());

        return Inertia::flash('success', __('Le don a été effectué avec succès.'))->back();
    }

    public function cancel(Group $group, Session $session, Donation $donation, CancelDonationAction $action): RedirectResponse
    {
        abort_unless($donation->session_id === $session->id, 404);
        $this->authorize('cancel', $donation);

        $action->execute($donation);

        return Inertia::flash('success', __('Le don a été annulé.'))->back();
    }
}

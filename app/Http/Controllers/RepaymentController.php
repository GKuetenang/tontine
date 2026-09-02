<?php

namespace App\Http\Controllers;

use App\Actions\Repayments\CreateRepaymentAction;
use App\Data\RepaymentData;
use App\Data\SessionData;
use App\Http\Requests\StoreRepaymentRequest;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepaymentController extends Controller
{
    public function index(Request $request, Group $group, Session $session): Response
    {
        $this->authorize('viewAny', [Repayment::class, $session]);
        $q = $request->string('q')->trim()->toString();
        $repayments = Repayment::query()
            ->whereHas('loan', fn ($query) => $query->where('session_id', $session->id))
            ->with('loan.membership.user')
            ->when($q, fn ($query) => $query->whereHas('loan.membership.user', fn ($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")))
            ->orderFromRequest($request)
            ->paginate()
            ->withQueryString();

        return Inertia::render('repayments/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'session' => SessionData::fromModel($session),
            'collection' => RepaymentData::collect($repayments),
            'q' => $q ?: null,
        ]);
    }

    public function store(StoreRepaymentRequest $request, Group $group, Session $session, Loan $loan, CreateRepaymentAction $action): RedirectResponse
    {
        abort_unless($loan->session_id === $session->id, 404);
        $this->authorize('create', [Repayment::class, $loan]);
        $action->execute($loan, $request->user(), $request->string('amount')->toString());

        return Inertia::flash('success', __('Le remboursement a été enregistré avec succès.'))->back();
    }
}

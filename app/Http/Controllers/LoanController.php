<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\CreateLoanAction;
use App\Data\LoanData;
use App\Data\SessionData;
use App\Enums\LoanStatus;
use App\Http\Requests\StoreLoanRequest;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends WithUserSearchController
{
    public function index(Request $request, Group $group, Session $session): Response
    {
        $this->authorize('viewAny', [Loan::class, $session]);
        $q = $request->string('q')->trim()->toString();
        $loans = $session->loans()
            ->with(['membership.user', 'repayments.loan.membership.user'])
            ->when($q, fn ($query) => $query->whereHas('membership.user', fn ($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")))
            ->orderFromRequest($request)
            ->paginate()
            ->withQueryString();

        return Inertia::render('loans/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'session' => SessionData::fromModel($session),
            'collection' => LoanData::collect($loans),
            'q' => $q ?: null,
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
            'statuses' => LoanStatus::getOptions(),
        ]);
    }

    public function store(StoreLoanRequest $request, Group $group, Session $session, CreateLoanAction $action): RedirectResponse
    {
        $this->authorize('create', [Loan::class, $session]);
        $membership = $session->participants()->active()->where('membership_id', $request->integer('membership_id'))->firstOrFail()->membership;
        $action->execute($session, $membership, $request->user(), $request->string('principal_amount')->toString(), $request->string('reason')->toString() ?: null);

        return Inertia::flash('success', __('Le prêt a été créé avec succès.'))->back();
    }

    public function approve(Group $group, Session $session, Loan $loan, ApproveLoanAction $action): RedirectResponse
    {
        abort_unless($loan->session_id === $session->id, 404);
        $this->authorize('approve', $loan);
        $action->execute($loan, request()->user());

        return Inertia::flash('success', __('Le prêt a été approuvé et décaissé.'))->back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ApproveLoanAction;
use App\Actions\Loans\CreateLoanAction;
use App\Data\LoanData;
use App\Data\SessionData;
use App\Http\Requests\StoreLoanRequest;
use App\Models\Loan;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends WithUserSearchController
{
    public function index(Tontine $tontine, Session $session): Response
    {
        $this->authorize('viewAny', [Loan::class, $session]);
        $loans = $session->loans()->with('membership.user')->latest()->paginate();

        return Inertia::render('loans/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug],
            'session' => SessionData::fromModel($session),
            'collection' => LoanData::collect($loans),
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
        ]);
    }

    public function store(StoreLoanRequest $request, Tontine $tontine, Session $session, CreateLoanAction $action): RedirectResponse
    {
        $this->authorize('create', [Loan::class, $session]);
        $membership = $session->participants()->active()->where('membership_id', $request->integer('membership_id'))->firstOrFail()->membership;
        $action->execute($session, $membership, $request->user(), $request->string('principal_amount')->toString(), $request->string('reason')->toString() ?: null);

        return Inertia::flash('success', __('Le prêt a été créé avec succès.'))->back();
    }

    public function approve(Tontine $tontine, Session $session, Loan $loan, ApproveLoanAction $action): RedirectResponse
    {
        abort_unless($loan->session_id === $session->id, 404);
        $this->authorize('approve', $loan);
        $action->execute($loan, request()->user());

        return Inertia::flash('success', __('Le prêt a été approuvé et décaissé.'))->back();
    }
}

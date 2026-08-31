<?php

namespace App\Http\Controllers;

use App\Actions\Repayments\CreateRepaymentAction;
use App\Data\RepaymentData;
use App\Data\SessionData;
use App\Http\Requests\StoreRepaymentRequest;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RepaymentController extends Controller
{
    public function index(Tontine $tontine, Session $session): Response
    {
        $this->authorize('viewAny', [Repayment::class, $session]);
        $repayments = Repayment::query()
            ->whereHas('loan', fn ($query) => $query->where('session_id', $session->id))
            ->with('loan.membership.user')
            ->latest('paid_at')
            ->paginate();

        return Inertia::render('repayments/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug],
            'session' => SessionData::fromModel($session),
            'collection' => RepaymentData::collect($repayments),
        ]);
    }

    public function store(StoreRepaymentRequest $request, Tontine $tontine, Session $session, Loan $loan, CreateRepaymentAction $action): RedirectResponse
    {
        abort_unless($loan->session_id === $session->id, 404);
        $this->authorize('create', [Repayment::class, $loan]);
        $action->execute($loan, $request->user(), $request->string('amount')->toString());

        return Inertia::flash('success', __('Le remboursement a été enregistré avec succès.'))->back();
    }
}

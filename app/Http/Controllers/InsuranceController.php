<?php

namespace App\Http\Controllers;

use App\Actions\Insurance\BuildInsuranceJournalAction;
use App\Actions\Insurance\CreateInsuranceContributionAction;
use App\Data\SessionData;
use App\Http\Requests\StoreInsuranceContributionRequest;
use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceController extends WithUserSearchController
{
    public function index(Request $request, Tontine $tontine, Session $session, BuildInsuranceJournalAction $buildJournal): Response
    {
        $this->authorize('viewAny', [InsuranceContribution::class, $session]);
        $filters = $request->validate([
            'q' => ['nullable', 'string'],
            'sort' => ['nullable', Rule::in(['amount', 'occurred_at', 'created_at'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $journal = $buildJournal->execute($session, $filters);

        return Inertia::render('insurance/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug],
            'session' => SessionData::fromModel($session),
            'collection' => $journal['collection'],
            'q' => $filters['q'] ?? null,
            'summary' => $journal['summary'],
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
        ]);
    }

    public function store(
        StoreInsuranceContributionRequest $request,
        Tontine $tontine,
        Session $session,
        CreateInsuranceContributionAction $createContribution,
    ): RedirectResponse {
        $this->authorize('create', [InsuranceContribution::class, $session]);
        $validated = $request->validated();
        $membership = Membership::query()->whereBelongsTo($tontine)->findOrFail($validated['membership_id']);

        $createContribution->execute(
            session: $session,
            membership: $membership,
            actor: $request->user(),
            amount: $validated['amount'],
            description: $validated['description'] ?? null,
            occurredAt: $validated['occurred_at'] ?? null,
        );

        return Inertia::flash('success', __('Le versement d’assurance a été enregistré avec succès.'))->back();
    }
}

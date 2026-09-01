<?php

namespace App\Http\Controllers;

use App\Actions\Penalties\SavePenaltyRuleAction;
use App\Data\PenaltyRuleData;
use App\Data\TontineData;
use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use App\Http\Requests\SavePenaltyRuleRequest;
use App\Models\PenaltyRule;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PenaltyRuleController extends Controller
{
    public function index(Request $request, Tontine $tontine): Response
    {
        Gate::authorize('viewAny', [PenaltyRule::class, $tontine]);
        $q = $request->string('q')->trim()->toString();

        return Inertia::render('penalty-rules/index', [
            'tontine' => TontineData::fromModel($tontine),
            'collection' => $tontine
                ->penaltyRules()
                ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
                ->orderFromRequest($request)
                ->paginate(10)
                ->withQueryString()
                ->through(
                    fn (PenaltyRule $rule): PenaltyRuleData => PenaltyRuleData::fromModel($rule),
                ),
            'q' => $q ?: null,
            'triggers' => PenaltyTrigger::getOptions(),
            'calculation_types' => PenaltyCalculationType::getOptions(),
            'grace_units' => PenaltyGraceUnit::getOptions(),
        ]);
    }

    public function store(
        SavePenaltyRuleRequest $request,
        Tontine $tontine,
        SavePenaltyRuleAction $action,
    ): RedirectResponse {
        Gate::authorize('create', [PenaltyRule::class, $tontine]);
        $action->execute($tontine, $request->validated());

        return Inertia::flash('success', __('Règle de pénalité créée avec succès.'))->back();
    }

    public function update(
        SavePenaltyRuleRequest $request,
        Tontine $tontine,
        PenaltyRule $penaltyRule,
        SavePenaltyRuleAction $action,
    ): RedirectResponse {
        Gate::authorize('update', $penaltyRule);
        $action->execute($tontine, $request->validated(), $penaltyRule);

        return Inertia::flash('success', __('Règle de pénalité mise à jour avec succès.'))->back();
    }
}

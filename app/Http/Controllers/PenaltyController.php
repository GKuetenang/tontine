<?php

namespace App\Http\Controllers;

use App\Actions\Penalties\CreateManualPenaltyAction;
use App\Actions\Penalties\WaivePenaltyAction;
use App\Data\PenaltyData;
use App\Data\SessionData;
use App\Http\Requests\StorePenaltyRequest;
use App\Http\Requests\WaivePenaltyRequest;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\Membership;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PenaltyController extends WithUserSearchController
{
    public function index(Request $request, Group $group, Session $session): Response
    {
        $this->authorize('viewAny', [Penalty::class, $session]);
        $q = $request->string('q')->trim()->toString();

        $penalties = Penalty::query()
            ->whereHas('meeting', fn ($query) => $query->where('session_id', $session->id))
            ->with(['membership.user', 'meeting'])
            ->when($q, fn ($query) => $query->whereHas('membership.user', fn ($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")))
            ->orderFromRequest($request)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('penalties/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug, 'currency' => $group->currency],
            'session' => SessionData::fromModel($session),
            'collection' => PenaltyData::collect($penalties),
            'q' => $q ?: null,
            'users' => fn () => Inertia::optional($this->membershipsInSession(...)),
            'meetings' => $session->meetings()->orderBy('number')->get(['id', 'title'])->map(fn (Meeting $meeting) => ['value' => (string) $meeting->id, 'label' => $meeting->title]),
            'rules' => $group->penaltyRules()->where('is_active', true)->orderBy('name')->get()->map(fn (PenaltyRule $rule) => ['value' => (string) $rule->id, 'label' => $rule->name]),
        ]);
    }

    public function store(StorePenaltyRequest $request, Group $group, Session $session, CreateManualPenaltyAction $action): RedirectResponse
    {
        $this->authorize('create', [Penalty::class, $session]);
        $action->execute(
            session: $session,
            meeting: $session->meetings()->findOrFail($request->integer('meeting_id')),
            membership: Membership::query()->findOrFail($request->integer('membership_id')),
            rule: $group->penaltyRules()->findOrFail($request->integer('penalty_rule_id')),
            creator: $request->user(),
            amount: $request->string('amount')->toString(),
            reason: $request->string('reason')->toString(),
        );

        return Inertia::flash('success', __('La pénalité a été ajoutée.'))->back();
    }

    public function waive(WaivePenaltyRequest $request, Group $group, Session $session, Penalty $penalty, WaivePenaltyAction $action): RedirectResponse
    {
        abort_unless($penalty->meeting->session_id === $session->id, 404);
        $this->authorize('update', $penalty);
        $action->execute($penalty, $request->user(), $request->string('reason')->toString());

        return Inertia::flash('success', __('Le membre a été exempté de cette pénalité.'))->back();
    }
}

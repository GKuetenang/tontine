<?php

namespace App\Http\Controllers;

use App\Actions\Mandates\ActivateMandateAction;
use App\Actions\Mandates\SaveMandateAction;
use App\Enums\GroupPermission;
use App\Enums\MandateStatus;
use App\Http\Requests\SaveMandateRequest;
use App\Models\Group;
use App\Models\Mandate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MandateController extends WithUserSearchController
{
    public function index(Request $request, Group $group): Response
    {
        Gate::authorize(GroupPermission::ViewMandates->value);
        $filters = $request->validate([
            'sort' => ['nullable', Rule::in(['name', 'starts_at', 'ends_at', 'status'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $mandates = $group->mandates()
            ->orderBy($filters['sort'] ?? 'starts_at', $filters['dir'] ?? 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Mandate $mandate): array => $this->serializeMandate($mandate));

        return Inertia::render('mandates/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'collection' => $mandates,
            'users' => fn () => Inertia::optional($this->activeMembershipsInGroup(...)),
        ]);
    }

    public function store(SaveMandateRequest $request, Group $group, SaveMandateAction $action): RedirectResponse
    {
        Gate::authorize(GroupPermission::CreateMandates->value);
        $action->execute($group, $request->user(), $request->validated());

        return Inertia::flash('success', __('Mandat créé avec succès.'))->back();
    }

    public function update(SaveMandateRequest $request, Group $group, Mandate $mandate, SaveMandateAction $action): RedirectResponse
    {
        Gate::authorize(GroupPermission::UpdateMandates->value);
        abort_unless($mandate->group_id === $group->id, 404);
        $action->execute($group, $request->user(), $request->validated(), $mandate);

        return Inertia::flash('success', __('Mandat mis à jour avec succès.'))->back();
    }

    public function activate(Request $request, Group $group, Mandate $mandate, ActivateMandateAction $action): RedirectResponse
    {
        Gate::authorize(GroupPermission::ActivateMandates->value);
        abort_unless($mandate->group_id === $group->id, 404);
        $action->execute($mandate);

        return Inertia::flash('success', __('Mandat activé avec succès.'))->back();
    }

    private function serializeMandate(Mandate $mandate): array
    {
        return [
            'id' => $mandate->id,
            'name' => $mandate->name,
            'starts_at' => $mandate->starts_at->toDateString(),
            'ends_at' => $mandate->ends_at->toDateString(),
            'status' => $mandate->status->value,
            'status_label' => $mandate->status->label(),
            'editable' => $mandate->status === MandateStatus::Draft,
            'assignable' => $mandate->status !== MandateStatus::Closed,
        ];
    }
}

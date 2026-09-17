<?php

namespace App\Http\Controllers;

use App\Actions\Mandates\DeleteMandateRoleAssignmentAction;
use App\Actions\Mandates\EndMandateRoleAssignmentAction;
use App\Actions\Mandates\SaveMandateRoleAssignmentAction;
use App\Actions\Mandates\SetMandateMemberRoleAction;
use App\Enums\GroupPermission;
use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Http\Requests\SaveMandateRoleAssignmentRequest;
use App\Models\Group;
use App\Models\Mandate;
use App\Models\MandateRoleAssignment;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class MandateRoleAssignmentController extends WithUserSearchController
{
    public function index(Request $request, Group $group, Mandate $mandate): Response
    {
        Gate::authorize(GroupPermission::ViewMandates->value);
        abort_unless($mandate->group_id === $group->id, 404);
        $filters = $request->validate([
            'q' => ['nullable', 'string'],
            'sort' => ['nullable', Rule::in(['name', 'member_number'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $q = trim((string) $request->input('q', ''));
        $sort = $filters['sort'] ?? 'member_number';
        $direction = $filters['dir'] ?? 'asc';

        $memberships = $group->memberships()->active()
            ->with([
                'user:id,first_name,name,email',
                'mandateRoleAssignments' => fn ($query) => $query
                    ->where('mandate_id', $mandate->id)
                    ->when($mandate->status === MandateStatus::Active, fn ($assignmentQuery) => $assignmentQuery
                        ->whereDate('starts_at', '<=', today())
                        ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhereDate('ends_at', '>=', today())))
                    ->latest('starts_at'),
            ])
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->when(
                $sort === 'name',
                fn ($query) => $query->orderBy(
                    User::query()->select('first_name')->whereColumn('users.id', 'memberships.user_id'),
                    $direction,
                )->orderBy(
                    User::query()->select('name')->whereColumn('users.id', 'memberships.user_id'),
                    $direction,
                ),
                fn ($query) => $query->orderBy('member_number', $direction),
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Membership $membership): array => [
                'id' => $membership->id,
                'member_number' => $membership->member_number,
                'name' => $membership->user->full_name,
                'email' => $membership->user->email,
                'role_id' => $membership->mandateRoleAssignments->first()?->role_id,
            ]);

        $roles = $group->roles()->where('name', '!=', GroupRole::Member->value)->orderBy('name')->get()
            ->map(fn (Role $role): array => [
                'value' => (string) $role->id,
                'label' => GroupRole::tryFrom($role->name)?->label() ?? $role->name,
            ]);
        $membership = new Membership;

        return Inertia::render('mandates/assignments/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'mandate' => ['id' => $mandate->id, 'name' => $mandate->name, 'status' => $mandate->status->value],
            'collection' => $memberships,
            'roles' => $roles,
            'q' => $q ?: null,
            'membership' => $membership,
            'users' => fn () => Inertia::optional(
                $this->users(...)
            ),
        ]);
    }

    public function update(
        Request $request,
        Group $group,
        Mandate $mandate,
        Membership $membership,
        SetMandateMemberRoleAction $action,
    ): RedirectResponse {
        Gate::authorize(GroupPermission::AssignMandateRoles->value);
        abort_unless($mandate->group_id === $group->id && $membership->group_id === $group->id, 404);
        $validated = $request->validate(['role_id' => ['nullable', 'integer', 'exists:roles,id']]);
        $role = isset($validated['role_id']) ? Role::query()->findOrFail($validated['role_id']) : null;
        $action->execute($mandate, $membership, $role, $request->user());

        return Inertia::flash('success', __('Responsabilité mise à jour avec succès.'))->back();
    }

    public function end(
        Request $request,
        Group $group,
        Mandate $mandate,
        MandateRoleAssignment $assignment,
        EndMandateRoleAssignmentAction $action,
    ): RedirectResponse {
        Gate::authorize(GroupPermission::AssignMandateRoles->value);
        abort_unless($mandate->group_id === $group->id && $assignment->mandate_id === $mandate->id, 404);
        $validated = $request->validate([
            'ends_at' => ['required', 'date'],
            'end_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $action->execute($assignment, $request->user(), $validated['ends_at'], $validated['end_reason'] ?? null);

        return Inertia::flash('success', __('Affectation terminée avec succès.'))->back();
    }

    public function store(
        SaveMandateRoleAssignmentRequest $request,
        Group $group,
        Mandate $mandate,
        SaveMandateRoleAssignmentAction $action,
    ): RedirectResponse {
        Gate::authorize(GroupPermission::AssignMandateRoles->value);
        abort_unless($mandate->group_id === $group->id, 404);
        $action->execute(
            $mandate,
            Membership::query()->findOrFail($request->integer('membership_id')),
            Role::query()->findOrFail($request->integer('role_id')),
            $request->user(),
            $request->string('starts_at')->toString(),
            $request->filled('ends_at') ? $request->string('ends_at')->toString() : null,
        );

        return Inertia::flash('success', __('Responsabilité attribuée avec succès.'))->back();
    }

    public function destroy(
        Request $request,
        Group $group,
        Mandate $mandate,
        MandateRoleAssignment $assignment,
        DeleteMandateRoleAssignmentAction $action,
    ): RedirectResponse {
        Gate::authorize(GroupPermission::AssignMandateRoles->value);
        abort_unless($mandate->group_id === $group->id && $assignment->mandate_id === $mandate->id, 404);
        $action->execute($assignment);

        return Inertia::flash('success', __('Affectation supprimée avec succès.'))->back();
    }
}

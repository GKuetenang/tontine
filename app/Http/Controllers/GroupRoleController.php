<?php

namespace App\Http\Controllers;

use App\Actions\Roles\SaveGroupRoleAction;
use App\Enums\GroupPermission;
use App\Enums\GroupRole;
use App\Http\Requests\SaveGroupRoleRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class GroupRoleController extends Controller
{
    public function index(Request $request, Group $group): Response
    {
        Gate::authorize(GroupPermission::ViewRoles->value);
        $filters = $request->validate([
            'q' => ['nullable', 'string'],
            'sort' => ['nullable', Rule::in(['name', 'created_at'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $q = trim($filters['q'] ?? '');

        $roles = $group->roles()
            ->with('permissions:id,name')
            ->withCount('users')
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy($filters['sort'] ?? 'name', $filters['dir'] ?? 'asc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Role $role): array => $this->serializeRole($role));

        $permissions = $request->user()
            ->getAllPermissions()
            ->pluck('name')
            ->intersect(GroupPermission::values())
            ->map(fn (string $name): array => [
                'value' => $name,
                'label' => GroupPermission::from($name)->label(),
                'group' => str($name)->before('.')->toString(),
                'group_label' => $this->permissionGroupLabel($name),
            ])
            ->values();

        return Inertia::render('roles/index', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'collection' => $roles,
            'permissions' => $permissions,
            'q' => $q ?: null,
            'can' => [
                'create' => Gate::allows(GroupPermission::CreateRoles->value),
                'update' => Gate::allows(GroupPermission::UpdateRoles->value),
            ],
        ]);
    }

    public function store(SaveGroupRoleRequest $request, Group $group, SaveGroupRoleAction $action): RedirectResponse
    {
        Gate::authorize(GroupPermission::CreateRoles->value);
        $action->execute($group, $request->string('name')->toString(), $request->validated('permissions', []));

        return Inertia::flash('success', __('Rôle créé avec succès.'))->back();
    }

    public function update(SaveGroupRoleRequest $request, Group $group, Role $role, SaveGroupRoleAction $action): RedirectResponse
    {
        Gate::authorize(GroupPermission::UpdateRoles->value);
        abort_unless((int) $role->group_id === $group->id, 404);
        abort_if($role->name === GroupRole::President->value, 403);
        $action->execute($group, $request->string('name')->toString(), $request->validated('permissions', []), $role);

        return Inertia::flash('success', __('Rôle mis à jour avec succès.'))->back();
    }

    private function serializeRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => GroupRole::tryFrom($role->name)?->label() ?? $role->name,
            'permissions' => $role->permissions->pluck('name')->values()->all(),
            'permissions_count' => $role->permissions->count(),
            'users_count' => $role->users_count,
            'editable' => $role->name !== GroupRole::President->value,
        ];
    }

    private function permissionGroupLabel(string $permission): string
    {
        return match (str($permission)->before('.')->toString()) {
            'groups' => __('Réunion'),
            'memberships' => __('Membres et rôles'),
            'roles' => __('Rôles et permissions'),
            'sessions' => __('Sessions'),
            'session-participants' => __('Participants'),
            'draws' => __('Tirages'),
            'meetings' => __('Assises'),
            'meeting-agenda' => __('Ordres du jour'),
            'meeting-attendances' => __('Présences'),
            'meeting-notes' => __('Notes'),
            'meeting-decisions' => __('Décisions'),
            'contributions' => __('Cotisations'),
            'donations' => __('Dons'),
            'loans' => __('Prêts'),
            'repayments' => __('Remboursements'),
            'penalties' => __('Pénalités'),
            'insurance' => __('Assurance'),
            'accounting' => __('Comptabilité'),
            'payouts' => __('Versements'),
            'reports' => __('Rapports'),
        };
    }
}

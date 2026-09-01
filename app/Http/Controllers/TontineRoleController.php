<?php

namespace App\Http\Controllers;

use App\Actions\Roles\SaveTontineRoleAction;
use App\Enums\TontinePermission;
use App\Enums\TontineRole;
use App\Http\Requests\SaveTontineRoleRequest;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TontineRoleController extends Controller
{
    public function index(Request $request, Tontine $tontine): Response
    {
        Gate::authorize(TontinePermission::ViewRoles->value);
        $filters = $request->validate([
            'q' => ['nullable', 'string'],
            'sort' => ['nullable', Rule::in(['name', 'created_at'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $q = trim($filters['q'] ?? '');

        $roles = $tontine->roles()
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
            ->intersect(TontinePermission::values())
            ->map(fn (string $name): array => [
                'value' => $name,
                'label' => TontinePermission::from($name)->label(),
                'group' => str($name)->before('.')->toString(),
                'group_label' => $this->permissionGroupLabel($name),
            ])
            ->values();

        return Inertia::render('roles/index', [
            'tontine' => ['id' => $tontine->id, 'name' => $tontine->name, 'slug' => $tontine->slug],
            'collection' => $roles,
            'permissions' => $permissions,
            'q' => $q ?: null,
            'can' => [
                'create' => Gate::allows(TontinePermission::CreateRoles->value),
                'update' => Gate::allows(TontinePermission::UpdateRoles->value),
            ],
        ]);
    }

    public function store(SaveTontineRoleRequest $request, Tontine $tontine, SaveTontineRoleAction $action): RedirectResponse
    {
        Gate::authorize(TontinePermission::CreateRoles->value);
        $action->execute($tontine, $request->string('name')->toString(), $request->validated('permissions', []));

        return Inertia::flash('success', __('Rôle créé avec succès.'))->back();
    }

    public function update(SaveTontineRoleRequest $request, Tontine $tontine, Role $role, SaveTontineRoleAction $action): RedirectResponse
    {
        Gate::authorize(TontinePermission::UpdateRoles->value);
        abort_unless((int) $role->tontine_id === $tontine->id, 404);
        abort_if($role->name === TontineRole::President->value, 403);
        $action->execute($tontine, $request->string('name')->toString(), $request->validated('permissions', []), $role);

        return Inertia::flash('success', __('Rôle mis à jour avec succès.'))->back();
    }

    private function serializeRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => TontineRole::tryFrom($role->name)?->label() ?? $role->name,
            'permissions' => $role->permissions->pluck('name')->values()->all(),
            'permissions_count' => $role->permissions->count(),
            'users_count' => $role->users_count,
            'editable' => $role->name !== TontineRole::President->value,
        ];
    }

    private function permissionGroupLabel(string $permission): string
    {
        return match (str($permission)->before('.')->toString()) {
            'tontines' => __('Tontine'),
            'memberships' => __('Membres et rôles'),
            'roles' => __('Rôles et permissions'),
            'sessions' => __('Sessions'),
            'session-participants' => __('Participants'),
            'draws' => __('Tirages'),
            'meetings' => __('Réunions'),
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

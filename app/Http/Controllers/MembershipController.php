<?php

namespace App\Http\Controllers;

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Memberships\DeactivateMembershipAction;
use App\Actions\Memberships\UpdateMembershipAction;
use App\Enums\MembershipStatus;
use App\Enums\TontineRole;
use App\Http\Requests\FormMembershipRequest;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class MembershipController extends WithUserSearchController
{
    public function index(
        Request $request,
        Tontine $tontine,
    ): Response {
        $this->authorize(
            'viewAny',
            [Membership::class, $tontine],
        );

        $search_query = $request->input('q') ?? '';
        $memberships = $tontine
            ->memberships()
            ->with([
                'user' => function ($query) use ($tontine): void {
                    $query
                        ->select(['id', 'first_name', 'name', 'email'])
                        ->with([
                            'roles' => fn($roleQuery) => $roleQuery->select(['roles.id', 'roles.name'])
                                ->where('roles.tontine_id', $tontine->id),
                        ]);
                },
                'inviter:id,name',
            ])
            ->when(
                \Str::of($search_query)->isNotEmpty(),
                function ($query) use ($search_query): void {
                    $query->whereHas(
                        'user',
                        fn($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search_query}%")
                            ->orWhere('first_name', 'like', "%{$search_query}%")
                            ->orWhere('email', 'like', "%{$search_query}%"),
                    );
                },
            )
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function (Membership $membership) use ($tontine): array {
                $role = $membership->user->roles->first();
                $roleEnum = $role
                    ? TontineRole::tryFrom($role->name)
                    : null;

                return [
                    'id' => $membership->id,
                    'tontine_id' => $membership->tontine_id,
                    'tontine_slug' => $tontine->slug,
                    'member_number' => $membership->member_number,
                    'status' => $membership->status->value,
                    'role' => $role
                        ? [
                            'name' => $role->name,
                            'label' => $roleEnum?->label() ?? $role->name,
                        ]
                        : null,
                    'user' => [
                        'id' => $membership->user->id,
                        'name' => $membership->user->full_name,
                        'email' => $membership->user->email,
                    ],

                ];
            });

        $roles = Role::query()
            ->where('tontine_id', $tontine->id)
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Role $role): array {
                $roleEnum = TontineRole::tryFrom($role->name);

                return [
                    'label' => $roleEnum?->label() ?? $role->name,
                    'value' => $role->name,
                ];
            });

        return Inertia::render('memberships/index', [
            'tontine' => fn() => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],
            'q' => fn() => $search_query,
            'collection' => fn() => $memberships,
            'roles' => fn() => $roles,
            'users' => fn() => Inertia::optional(
                $this->users(...)
            ),
            'membership' => fn() => new Membership,
            'statuses' => fn() => MembershipStatus::getOptions(),
        ]);
    }

    public function store(
        FormMembershipRequest $request,
        Tontine $tontine,
        CreateMembershipAction $createMembership,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [Membership::class, $tontine],
        );

        $validated = $request->validated();

        $user = User::query()
            ->findOrFail($validated['user_id']);

        $createMembership->execute(
            tontine: $tontine,
            user: $user,
            roleName: $validated['role'],
            invitedBy: $request->user(),
        );

        return Inertia::flash(
            'success',
            __('Le membre a été ajouté avec succès.'),
        )->back();
    }

    public function destroy(
        Request $request,
        Tontine $tontine,
        Membership $membership,
        DeactivateMembershipAction $deactivateMembership,
    ): RedirectResponse {
        $this->authorize('delete', $membership);

        /*
         * Sécurité supplémentaire. scoped() doit déjà garantir
         * cette appartenance.
         */
        abort_unless(
            $membership->tontine_id === $tontine->id,
            404,
        );

        $deactivateMembership->execute($membership);

        return Inertia::flash(
            'success',
            __('Le membre a été désactivé avec succès.'),
        )->back();
    }

    public function update(
        FormMembershipRequest $request,
        Tontine $tontine,
        Membership $membership,
        UpdateMembershipAction $updateMembership,
    ): RedirectResponse {
        $this->authorize('update', $membership);

        abort_unless(
            $membership->tontine_id === $tontine->id,
            404,
        );
        $validated = $request->validated();


        $updateMembership->execute(
            tontine: $tontine,
            membership: $membership,
            roleName: $validated['role'],
            status: MembershipStatus::tryFrom($validated['status']),
        );

        return Inertia::flash(
            'success',
            __('Le membre a été modifié avec succès.'),
        )->back();
    }
}

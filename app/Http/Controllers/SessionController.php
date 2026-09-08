<?php

namespace App\Http\Controllers;

use App\Actions\Sessions\ActivateSessionAction;
use App\Actions\Sessions\CloseSessionAction;
use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\DeleteSessionAction;
use App\Actions\Sessions\ForceDeleteSessionAction;
use App\Actions\Sessions\RestoreSessionAction;
use App\Actions\Sessions\UpdateSessionAction;
use App\Data\SessionData;
use App\Enums\DrawAllocationMode;
use App\Http\Requests\FormSessionRequest;
use App\Models\Group;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(
        Request $request,
        Group $group,
    ): Response {
        $this->authorize(
            'viewAny',
            [Session::class, $group],
        );

        $query = $group->sessions()
            ->withCount('participants')
            ->orderFromRequest($request);
        $search_query = $request->input('q');

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$search_query}%")
                ->orWhere('description', 'like', "%{$search_query}%");
        }

        $sessions = SessionData::collect(
            $query->paginate(10)->withQueryString(),
        );

        return Inertia::render('sessions/index', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
            ],
            'collection' => $sessions,
            'session' => fn () => new Session,
            'draw_allocation_modes' => DrawAllocationMode::getOptions(),
        ]);
    }

    public function store(
        FormSessionRequest $request,
        Group $group,
        CreateSessionAction $action,
    ): RedirectResponse {
        $this->authorize('create', [Session::class, $group]);
        $action->execute(
            group: $group,
            attributes: $request->validated(),
        );

        return Inertia::flash(
            'success',
            __('La session a été créée avec succès.'),
        )->back();
    }

    public function show(
        Group $group,
        Session $session,
    ): Response {
        $this->authorize('view', $session);

        $session->loadCount([
            'participants',
            'meetings',
        ]);

        return Inertia::render('sessions/show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'currency' => $group->currency,
            ],

            'session' => SessionData::fromModel(
                $session,
            ),
        ]);
    }

    public function trash(Request $request, Group $group): Response
    {
        $this->authorize('viewAny', [Session::class, $group]);
        $q = $request->string('q')->trim()->toString();

        return Inertia::render('sessions/trash', [
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'collection' => SessionData::collect(
                $group->sessions()
                    ->onlyTrashed()
                    ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
                    ->orderFromRequest($request)
                    ->paginate(10)
                    ->withQueryString(),
            ),
            'q' => $q ?: null,
        ]);
    }

    public function restore(Group $group, Session $session, RestoreSessionAction $action): RedirectResponse
    {
        $this->authorize('restore', $session);
        $action->execute($session);

        return Inertia::flash('success', __('La session a été restaurée avec succès.'))->back();
    }

    public function forceDelete(Group $group, Session $session, ForceDeleteSessionAction $action): RedirectResponse
    {
        $this->authorize('forceDelete', $session);
        $action->execute($session);

        return Inertia::flash('success', __('La session a été supprimée définitivement.'))->back();
    }

    public function update(
        FormSessionRequest $request,
        Group $group,
        Session $session,
        UpdateSessionAction $action,
    ): RedirectResponse {
        $this->authorize('update', [Session::class, $session]);
        $action->execute(
            session: $session,
            attributes: $request->validated(),
        );

        return Inertia::flash(
            'success',
            __('La session a été modifiée avec succès.'),
        )->back();
    }

    public function destroy(
        Group $group,
        Session $session,
        DeleteSessionAction $action,
    ): RedirectResponse {
        $this->authorize('delete', $session);

        $action->execute($session);

        return Inertia::flash(
            'success',
            __('La session a été supprimée avec succès.'),
        )->back();
    }

    public function activate(
        Group $group,
        Session $session,
        ActivateSessionAction $action,
    ): RedirectResponse {
        $this->authorize('activate', $session);

        $action->execute($session);

        return Inertia::flash(
            'success',
            __('La session a été activée avec succès.'),
        )->back();
    }

    public function close(
        Group $group,
        Session $session,
        CloseSessionAction $action,
    ): RedirectResponse {
        $this->authorize('close', $session);

        $action->execute($session);

        return Inertia::flash(
            'success',
            __('La session a été fermée avec succès.'),
        )->back();
    }
}

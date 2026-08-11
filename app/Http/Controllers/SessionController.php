<?php

namespace App\Http\Controllers;

use App\Actions\Sessions\ActivateSessionAction;
use App\Actions\Sessions\CloseSessionAction;
use App\Actions\Sessions\CreateSessionAction;
use App\Actions\Sessions\DeleteSessionAction;
use App\Actions\Sessions\UpdateSessionAction;
use App\Data\SessionData;
use App\Http\Requests\FormSessionRequest;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(
        Request $request,
        Tontine $tontine,
    ): Response {
        $this->authorize(
            'viewAny',
            [Session::class, $tontine],
        );

        $query = Session::query()
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
            'tontine' => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],
            'collection' => $sessions,
            'session' => fn() => new Session(),
        ]);
    }

    public function store(
        FormSessionRequest $request,
        Tontine $tontine,
        CreateSessionAction $action,
    ): RedirectResponse {
        $this->authorize('create', [Session::class, $tontine]);
        $action->execute(
            tontine: $tontine,
            attributes: $request->validated(),
        );

        return Inertia::flash(
            'success',
            __('La session a été créée avec succès.'),
        )->back();
    }

    public function update(
        FormSessionRequest $request,
        Tontine $tontine,
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
        Tontine $tontine,
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
        Tontine $tontine,
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
        Tontine $tontine,
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

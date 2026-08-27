<?php

namespace App\Http\Controllers;

use App\Actions\Draws\ConfirmDrawAction;
use App\Actions\Draws\DeleteDrawAction;
use App\Actions\Draws\GenerateDrawAction;
use App\Actions\Draws\ResetDrawAction;
use App\Actions\Draws\SwapDrawEntriesAction;
use App\Data\DrawData;
use App\Data\SessionData;
use App\Data\TontineData;
use App\Http\Requests\SwapDrawEntriesRequest;
use App\Models\Draw;
use App\Models\DrawEntry;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DrawController extends Controller
{
    public function show(
        Tontine $tontine,
        Session $session,
    ): Response {
        $this->authorize(
            'view',
            [
                Draw::class,
                $session,
            ],
        );

        $draw = $session
            ->draw()
            ->with([
                'session.meetings',

                'entries.sessionParticipant.membership.user',
            ])
            ->first();

        return Inertia::render(
            'draws/show',
            [
                'tontine' =>
                TontineData::fromModel(
                    $tontine,
                ),

                'session' =>
                SessionData::fromModel(
                    $session,
                ),

                'draw' =>
                $draw
                    ? DrawData::fromModel(
                        $draw,
                    )
                    : null,
            ],
        );
    }

    public function generate(
        Tontine $tontine,
        Session $session,
        GenerateDrawAction $action,
    ): RedirectResponse {
        Gate::authorize(
            'generate',
            [Draw::class, $session],
        );

        $action->execute(
            session: $session,
            user: request()->user(),
        );

        return back()->with(
            'success',
            __('Le tirage a été généré avec succès.'),
        );
    }

    public function confirm(
        Tontine $tontine,
        Session $session,
        ConfirmDrawAction $action,
    ): RedirectResponse {
        $draw = $session
            ->draw()
            ->firstOrFail();

        Gate::authorize('confirm', $draw);

        $action->execute(
            draw: $draw,
            user: request()->user(),
        );

        return back()->with(
            'success',
            __('Le tirage a été confirmé avec succès.'),
        );
    }

    public function reset(
        Tontine $tontine,
        Session $session,
        ResetDrawAction $action,
    ): RedirectResponse {
        $draw = $session
            ->draw()
            ->firstOrFail();

        Gate::authorize('reset', $draw);

        $action->execute($draw);

        return back()->with(
            'success',
            __('Le tirage a été réinitialisé avec succès.'),
        );
    }

    public function swap(
        SwapDrawEntriesRequest $request,
        Tontine $tontine,
        Session $session,
        SwapDrawEntriesAction $action,
    ): RedirectResponse {
        $draw = Draw::query()
            ->where(
                'session_id',
                $session->id,
            )
            ->firstOrFail();

        $this->authorize(
            'update',
            $draw,
        );

        $source = DrawEntry::query()
            ->where(
                'draw_id',
                $draw->id,
            )
            ->findOrFail(
                $request->integer(
                    'source_entry_id',
                ),
            );

        $target = DrawEntry::query()
            ->where(
                'draw_id',
                $draw->id,
            )
            ->findOrFail(
                $request->integer(
                    'target_entry_id',
                ),
            );

        $action->execute(
            draw: $draw,
            source: $source,
            target: $target,
        );

        return Inertia::flash(
            'success',
            __('Les positions ont été permutées avec succès.'),
        )->back();
    }

    public function destroy(
        Tontine $tontine,
        Session $session,
        DeleteDrawAction $action,
    ): RedirectResponse {
        $draw = $session
            ->draw()
            ->firstOrFail();

        Gate::authorize('delete', $draw);

        $action->execute($draw);

        return back()->with(
            'success',
            __('Le tirage a été supprimé avec succès.'),
        );
    }
}

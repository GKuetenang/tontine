<?php

namespace App\Http\Controllers;

use App\Actions\Sessions\AddSessionParticipantAction;
use App\Actions\Sessions\ReactivateSessionParticipantAction;
use App\Actions\Sessions\RemoveSessionParticipantAction;
use App\Actions\Sessions\UpdateSessionParticipantAction;
use App\Data\SessionData;
use App\Data\SessionParticipantData;
use App\Data\TontineData;
use App\Http\Requests\SessionParticipants\StoreSessionParticipantRequest;
use App\Http\Requests\SessionParticipants\UpdateSessionParticipantRequest;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionParticipantController extends Controller
{

    public function index(
        Tontine $tontine,
        Session $session,
    ): Response {
        // $this->authorize(
        //     'viewAny',
        //     [SessionParticipant::class, $session],
        // );

        $participants = $session
            ->participants()
            ->with([
                'membership.user',
            ])
            ->latest()
            ->paginate(20);

        return Inertia::render(
            'session-participants/index',
            [
                'tontine' => TontineData::from($tontine),

                'session' => SessionData::from($session),

                'collection' =>
                SessionParticipantData::collect(
                    $participants
                ),
            ],
        );
    }

    public function store(
        StoreSessionParticipantRequest $request,
        Tontine $tontine,
        Session $session,
        AddSessionParticipantAction $action,
    ): RedirectResponse {
        $membership = Membership::query()
            ->where('tontine_id', $tontine->id)
            ->findOrFail(
                $request->integer('membership_id')
            );

        $action->execute(
            session: $session,
            membership: $membership,
            contributionAmount: $request->integer('contribution_amount'),
            drawEntriesCount: $request->filled('draw_entries_count')
                ? $request->integer('draw_entries_count')
                : null,
        );

        return Inertia::flash(
            'success',
            __('Le participant a été ajouté avec succès.'),
        )->back();
    }

    public function update(
        UpdateSessionParticipantRequest $request,
        Tontine $tontine,
        Session $session,
        SessionParticipant $participant,
        UpdateSessionParticipantAction $action,
    ): RedirectResponse {
        $action->execute(
            participant: $participant,
            contributionAmount: $request->integer('contribution_amount'),
            drawEntriesCount: $request->filled('draw_entries_count')
                ? $request->integer('draw_entries_count')
                : null,
        );

        return Inertia::flash(
            'success',
            __('Le participant a été modifié avec succès.'),
        )->back();
    }

    public function destroy(
        Tontine $tontine,
        Session $session,
        SessionParticipant $participant,
        RemoveSessionParticipantAction $action,
    ): RedirectResponse {
        $this->authorize(
            'delete',
            $participant,
        );

        $action->execute($participant);

        return Inertia::flash(
            'success',
            __('Le participant a été retiré de la session.'),
        )->back();
    }

    public function reactivate(
        Tontine $tontine,
        Session $session,
        SessionParticipant $participant,
        ReactivateSessionParticipantAction $action,
    ): RedirectResponse {
        $this->authorize(
            'reactivate',
            $participant,
        );

        $action->execute($participant);

        return Inertia::flash(
            'success',
            __('Le participant a été réactivé avec succès.'),
        )->back();
    }
}

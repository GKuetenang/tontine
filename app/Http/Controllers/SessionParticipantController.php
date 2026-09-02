<?php

namespace App\Http\Controllers;

use App\Actions\SessionParticipants\AddSessionParticipantAction;
use App\Actions\SessionParticipants\ReactivateSessionParticipantAction;
use App\Actions\SessionParticipants\RemoveSessionParticipantAction;
use App\Actions\SessionParticipants\UpdateSessionParticipantAction;
use App\Data\GroupData;
use App\Data\SessionData;
use App\Data\SessionParticipantData;
use App\Http\Requests\SessionParticipants\StoreSessionParticipantRequest;
use App\Http\Requests\SessionParticipants\UpdateSessionParticipantRequest;
use App\Models\Group;
use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SessionParticipantController extends WithUserSearchController
{
    public function index(
        Group $group,
        Session $session,
    ): Response {
        $this->authorize(
            'viewAny',
            [SessionParticipant::class, $session],
        );

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
                'group' => GroupData::from($group),
                'session' => SessionData::from($session),
                'collection' => SessionParticipantData::collect(
                    $participants
                ),
                'users' => fn () => Inertia::optional(
                    $this->membershipsInGroup(...)
                ),
            ],
        );
    }

    public function store(
        StoreSessionParticipantRequest $request,
        Group $group,
        Session $session,
        AddSessionParticipantAction $action,
    ): RedirectResponse {
        $membership = Membership::query()
            ->where('group_id', $group->id)
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
        Group $group,
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
        Group $group,
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
        Group $group,
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

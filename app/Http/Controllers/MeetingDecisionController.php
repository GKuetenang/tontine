<?php

namespace App\Http\Controllers;

use App\Actions\MeetingDecisions\AddMeetingDecisionAction;
use App\Actions\MeetingDecisions\DeleteMeetingDecisionAction;
use App\Actions\MeetingDecisions\UpdateMeetingDecisionAction;
use App\Http\Requests\FormMeetingDecisionRequest;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingDecision;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class MeetingDecisionController extends Controller
{
    public function store(
        FormMeetingDecisionRequest $request,
        Group $group,
        Session $session,
        Meeting $meeting,
        AddMeetingDecisionAction $action,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [
                MeetingDecision::class,
                $meeting,
            ],
        );

        $agendaItem =
            $this->resolveAgendaItem(
                $request,
            );

        $action->execute(
            meeting: $meeting,
            creator: $request->user(),

            title: $request->string(
                'title',
            )->toString(),

            description: $request->filled(
                'description',
            )
                ? $request->string(
                    'description',
                )->toString()
                : null,

            agendaItem: $agendaItem,
        );

        return Inertia::flash(
            'success',
            __(
                'La décision a été ajoutée avec succès.'
            ),
        )->back();
    }

    public function update(
        FormMeetingDecisionRequest $request,
        Group $group,
        Session $session,
        Meeting $meeting,
        MeetingDecision $decision,
        UpdateMeetingDecisionAction $action,
    ): RedirectResponse {
        abort_unless(
            $decision->meeting_id
                === $meeting->id,
            404,
        );

        $this->authorize(
            'update',
            $decision,
        );

        $agendaItem =
            $this->resolveAgendaItem(
                $request,
            );

        $action->execute(
            decision: $decision,

            title: $request->string(
                'title',
            )->toString(),

            description: $request->filled(
                'description',
            )
                ? $request->string(
                    'description',
                )->toString()
                : null,

            agendaItem: $agendaItem,
        );

        return Inertia::flash(
            'success',
            __(
                'La décision a été modifiée avec succès.'
            ),
        )->back();
    }

    public function destroy(
        Group $group,
        Session $session,
        Meeting $meeting,
        MeetingDecision $decision,
        DeleteMeetingDecisionAction $action,
    ): RedirectResponse {
        abort_unless(
            $decision->meeting_id
                === $meeting->id,
            404,
        );

        $this->authorize(
            'delete',
            $decision,
        );

        $action->execute(
            $decision,
        );

        return Inertia::flash(
            'success',
            __(
                'La décision a été supprimée avec succès.'
            ),
        )->back();
    }

    private function resolveAgendaItem(
        FormMeetingDecisionRequest $request,
    ): ?MeetingAgendaItem {
        if (
            ! $request->filled(
                'meeting_agenda_item_id',
            )
        ) {
            return null;
        }

        return MeetingAgendaItem::query()
            ->findOrFail(
                $request->integer(
                    'meeting_agenda_item_id',
                ),
            );
    }
}

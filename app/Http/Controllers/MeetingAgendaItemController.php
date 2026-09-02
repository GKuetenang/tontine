<?php

namespace App\Http\Controllers;

use App\Actions\MeetingAgendaItems\AddMeetingAgendaItemAction;
use App\Actions\MeetingAgendaItems\RemoveMeetingAgendaItemAction;
use App\Actions\MeetingAgendaItems\ReorderMeetingAgendaItemsAction;
use App\Actions\MeetingAgendaItems\UpdateMeetingAgendaItemAction;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingAgendaItemController extends Controller
{
    public function store(
        Request $request,
        Group $group,
        Session $session,
        Meeting $meeting,
        AddMeetingAgendaItemAction $action,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [MeetingAgendaItem::class, $meeting],
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $action->execute(
            meeting: $meeting,
            title: $validated['title'],
            description: $validated['description'] ?? null,
        );

        return Inertia::flash(
            'success',
            __(
                'Le point a été ajouté à l’ordre du jour avec succès.'
            ),
        )->back();
    }

    public function update(
        Request $request,
        Group $group,
        Session $session,
        Meeting $meeting,
        MeetingAgendaItem $agendaItem,
        UpdateMeetingAgendaItemAction $action,
    ): RedirectResponse {
        $this->authorize(
            'update',
            $agendaItem,
        );

        abort_unless(
            $agendaItem->meeting_id === $meeting->id,
            404,
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $action->execute(
            item: $agendaItem,
            title: $validated['title'],
            description: $validated['description'] ?? null,
        );

        return Inertia::flash(
            'success',
            __(
                'Le point de l’ordre du jour a été modifié avec succès.'
            ),
        )->back();
    }

    public function destroy(
        Group $group,
        Session $session,
        Meeting $meeting,
        MeetingAgendaItem $agendaItem,
        RemoveMeetingAgendaItemAction $action,
    ): RedirectResponse {
        $this->authorize(
            'delete',
            $agendaItem,
        );

        abort_unless(
            $agendaItem->meeting_id === $meeting->id,
            404,
        );

        $action->execute(
            item: $agendaItem,
        );

        return Inertia::flash(
            'success',
            __(
                'Le point a été retiré de l’ordre du jour avec succès.'
            ),
        )->back();
    }

    public function reorder(
        Request $request,
        Group $group,
        Session $session,
        Meeting $meeting,
        ReorderMeetingAgendaItemsAction $action,
    ): RedirectResponse {
        $this->authorize(
            'reorder',
            [MeetingAgendaItem::class, $meeting],
        );

        $validated = $request->validate([
            'items' => [
                'required',
                'array',
            ],

            'items.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ]);

        $action->execute(
            meeting: $meeting,
            itemIds: $validated['items'],
        );

        return Inertia::flash(
            'success',
            __(
                'L’ordre du jour a été réorganisé avec succès.'
            ),
        )->back();
    }
}

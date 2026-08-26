<?php

namespace App\Http\Controllers;

use App\Actions\MeetingNotes\AddMeetingNoteAction;
use App\Actions\MeetingNotes\DeleteMeetingNoteAction;
use App\Actions\MeetingNotes\UpdateMeetingNoteAction;
use App\Http\Requests\FormMeetingNoteRequest;
use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use App\Models\MeetingNote;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class MeetingNoteController extends Controller
{
    public function store(
        FormMeetingNoteRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        AddMeetingNoteAction $action,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [MeetingNote::class, $meeting],
        );

        $agendaItem = $request->filled(
            'meeting_agenda_item_id',
        )
            ? MeetingAgendaItem::query()
            ->findOrFail(
                $request->integer(
                    'meeting_agenda_item_id',
                ),
            )
            : null;

        $action->execute(
            meeting: $meeting,
            creator: $request->user(),
            content: $request->string(
                'content',
            )->toString(),
            agendaItem: $agendaItem,
        );

        return Inertia::flash(
            'success',
            __('La note a été ajoutée avec succès.'),
        )->back();
    }

    public function update(
        FormMeetingNoteRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        MeetingNote $note,
        UpdateMeetingNoteAction $action,
    ): RedirectResponse {
        abort_unless(
            $note->meeting_id === $meeting->id,
            404,
        );

        $this->authorize(
            'update',
            $note,
        );

        $agendaItem = $request->filled(
            'meeting_agenda_item_id',
        )
            ? MeetingAgendaItem::query()
            ->findOrFail(
                $request->integer(
                    'meeting_agenda_item_id',
                ),
            )
            : null;

        $action->execute(
            note: $note,
            content: $request->string(
                'content',
            )->toString(),
            agendaItem: $agendaItem,
        );

        return Inertia::flash(
            'success',
            __('La note a été modifiée avec succès.'),
        )->back();
    }

    public function destroy(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        MeetingNote $note,
        DeleteMeetingNoteAction $action,
    ): RedirectResponse {
        abort_unless(
            $note->meeting_id === $meeting->id,
            404,
        );

        $this->authorize(
            'delete',
            $note,
        );

        $action->execute($note);

        return Inertia::flash(
            'success',
            __('La note a été supprimée avec succès.'),
        )->back();
    }
}

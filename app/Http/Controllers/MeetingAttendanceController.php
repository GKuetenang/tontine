<?php

namespace App\Http\Controllers;

use App\Actions\MeetingAttendances\UpdateMeetingAttendanceAction;
use App\Enums\AttendanceStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\Session;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MeetingAttendanceController extends Controller
{
    public function update(
        Request $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        MeetingAttendance $attendance,
        UpdateMeetingAttendanceAction $action,
    ): RedirectResponse {
        $this->authorize(
            'update',
            $attendance,
        );

        abort_unless(
            $attendance->meeting_id === $meeting->id,
            404,
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::enum(
                    AttendanceStatus::class,
                ),
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $action->execute(
            attendance: $attendance,
            status: AttendanceStatus::from(
                $validated['status'],
            ),
            note: $validated['note'] ?? null,
        );

        return Inertia::flash(
            'success',
            __('La présence a été mise à jour avec succès.'),
        )->back();
    }
}

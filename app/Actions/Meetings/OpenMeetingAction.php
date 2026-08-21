<?php

namespace App\Actions\Meetings;

use App\Enums\AttendanceStatus;
use App\Enums\MeetingStatus;
use App\Enums\SessionStatus;
use App\Models\Meeting;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OpenMeetingAction
{
    public function execute(Meeting $meeting): Meeting
    {
        return DB::transaction(
            function () use ($meeting): Meeting {
                if (
                    $meeting->status
                    !== MeetingStatus::Scheduled
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Seule une réunion prévue peut être ouverte.'
                        ),
                    ]);
                }

                if (
                    $meeting->session->status
                    !== SessionStatus::Active
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'La session doit être active pour ouvrir une réunion.'
                        ),
                    ]);
                }

                $participants = $meeting
                    ->session
                    ->participants()
                    ->active()
                    ->get();

                if ($participants->isEmpty()) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'La réunion ne peut pas être ouverte sans participant actif.'
                        ),
                    ]);
                }

                foreach ($participants as $participant) {
                    $meeting
                        ->attendances()
                        ->create([
                            'session_participant_id' =>
                            $participant->id,

                            'status' =>
                            AttendanceStatus::Pending,
                        ]);

                    $meeting
                        ->contributions()
                        ->create([
                            'session_participant_id' =>
                            $participant->id,

                            'amount_due' =>
                            $participant->contribution_amount,
                        ]);
                }

                $meeting->forceFill([
                    'status' =>
                    MeetingStatus::InProgress,

                    'opened_at' => now(),
                ])->save();

                return $meeting
                    ->refresh()
                    ->load([
                        'attendances.sessionParticipant.membership.user',
                    ]);
            },
        );
    }
}

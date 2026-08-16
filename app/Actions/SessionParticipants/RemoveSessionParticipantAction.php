<?php

namespace App\Actions\SessionParticipants;

use App\Enums\SessionStatus;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RemoveSessionParticipantAction
{
    public function execute(
        SessionParticipant $participant,
    ): SessionParticipant {
        return DB::transaction(function () use (
            $participant,
        ): SessionParticipant {
            $participant->loadMissing([
                'session',
            ]);

            if (
                $participant->session->status
                !== SessionStatus::Draft
            ) {
                throw ValidationException::withMessages([
                    'participant' => __(
                        'Un participant ne peut être retiré que lorsque la session est en préparation.'
                    ),
                ]);
            }

            if (! $participant->is_active) {
                throw ValidationException::withMessages([
                    'participant' => __(
                        'Ce participant a déjà été retiré de la session.'
                    ),
                ]);
            }

            $participant->forceFill([
                'is_active' => false,
                'left_at' => now(),
            ]);

            $participant->save();

            return $participant->refresh();
        });
    }
}

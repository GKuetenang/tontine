<?php

namespace App\Actions\SessionParticipants;

use App\Enums\SessionStatus;
use App\Models\SessionParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReactivateSessionParticipantAction
{
    public function execute(
        SessionParticipant $participant,
    ): SessionParticipant {
        return DB::transaction(function () use (
            $participant,
        ): SessionParticipant {
            $participant->loadMissing([
                'session',
                'membership',
            ]);

            if (
                $participant->session->status
                !== SessionStatus::Draft
            ) {
                throw ValidationException::withMessages([
                    'participant' => __(
                        'Un participant ne peut être réactivé que lorsque la session est en préparation.'
                    ),
                ]);
            }

            if ($participant->is_active) {
                throw ValidationException::withMessages([
                    'participant' => __(
                        'Ce participant est déjà actif.'
                    ),
                ]);
            }

            if (! $participant->membership->isActive()) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Le membre doit être actif dans la réunion avant de pouvoir être réintégré à la session.'
                    ),
                ]);
            }

            $participant->forceFill([
                'is_active' => true,
                'left_at' => null,
            ]);

            $participant->save();

            return $participant->refresh();
        });
    }
}

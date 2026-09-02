<?php

namespace App\Actions\MeetingDecisions;

use App\Enums\MeetingStatus;
use App\Models\MeetingDecision;
use Illuminate\Validation\ValidationException;

final class DeleteMeetingDecisionAction
{
    public function execute(
        MeetingDecision $decision,
    ): void {
        if (
            $decision->meeting->status
            !== MeetingStatus::InProgress
        ) {
            throw ValidationException::withMessages([
                'meeting' => __(
                    'Une décision ne peut être supprimée que pendant une assise en cours.'
                ),
            ]);
        }

        $decision->delete();
    }
}

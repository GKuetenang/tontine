<?php

namespace App\Actions\MeetingDecisions;

use App\Enums\MeetingStatus;
use App\Models\MeetingDecision;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteMeetingDecisionAction
{
    public function execute(
        MeetingDecision $decision,
    ): void {
        DB::transaction(
            function () use ($decision): void {
                if (
                    $decision->meeting->status
                    !== MeetingStatus::InProgress
                ) {
                    throw ValidationException::withMessages([
                        'meeting' => __(
                            'Les décisions ne peuvent être supprimées que pendant une réunion en cours.'
                        ),
                    ]);
                }

                $decision->delete();
            },
        );
    }
}

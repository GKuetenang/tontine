<?php

namespace App\Actions\Meetings;

use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteMeetingAction
{
    public function execute(Meeting $meeting): void
    {
        DB::transaction(function () use ($meeting): void {
            $meeting = Meeting::query()->lockForUpdate()->findOrFail($meeting->id);

            if (! $meeting->isScheduled() && ! $meeting->isCancelled()) {
                throw ValidationException::withMessages([
                    'meeting' => __('Seule une assise planifiée ou annulée peut être placée dans la corbeille.'),
                ]);
            }

            $meeting->delete();
        });
    }
}

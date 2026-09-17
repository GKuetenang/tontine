<?php

namespace App\Actions\Meetings;

use App\Models\Meeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ForceDeleteMeetingAction
{
    public function execute(Meeting $meeting): void
    {
        DB::transaction(function () use ($meeting): void {
            $meeting = Meeting::onlyTrashed()->lockForUpdate()->findOrFail($meeting->id);

            $hasHistory = $meeting->agendaItems()->exists()
                || $meeting->attendances()->exists()
                || $meeting->contributions()->exists()
                || $meeting->notes()->exists()
                || $meeting->decisions()->exists()
                || $meeting->payouts()->exists()
                || $meeting->penalties()->exists();

            if ($hasHistory) {
                throw ValidationException::withMessages([
                    'meeting' => __('Une assise contenant un historique ne peut pas être supprimée définitivement.'),
                ]);
            }

            $meeting->forceDelete();
        });
    }
}

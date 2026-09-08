<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ForceDeleteSessionAction
{
    public function execute(Session $session): void
    {
        DB::transaction(function () use ($session): void {
            $hasHistory = $session->draw()->withTrashed()->exists()
                || $session->meetings()->exists()
                || $session->transactions()->exists()
                || $session->donations()->exists()
                || $session->loans()->exists()
                || $session->insuranceContributions()->exists();

            if ($hasHistory) {
                throw ValidationException::withMessages([
                    'session' => __('Une session contenant un historique métier ou financier ne peut pas être supprimée définitivement.'),
                ]);
            }

            $session->meetingSchedule()->delete();
            $session->participants()->delete();
            $session->forceDelete();
        });
    }
}

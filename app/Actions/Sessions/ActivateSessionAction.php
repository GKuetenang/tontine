<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ActivateSessionAction
{
    public function execute(Session $session): Session
    {
        return DB::transaction(function () use (
            $session,
        ): Session {
            if ($session->is_closed) {
                throw ValidationException::withMessages([
                    'session' => __('Une session fermée ne peut pas être activée.'),
                ]);
            }

            if ($session->is_active) {
                return $session;
            }

            $session->tontine
                ->sessions()
                ->whereKeyNot($session->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                ]);

            $session->forceFill([
                'is_active' => true,
                'activated_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }
}

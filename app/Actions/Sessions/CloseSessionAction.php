<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CloseSessionAction
{
    public function execute(Session $session): Session
    {
        return DB::transaction(function () use (
            $session,
        ): Session {
            if ($session->is_closed) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Cette session est déjà fermée.'
                    ),
                ]);
            }

            $session->forceFill([
                'is_active' => false,
                'is_closed' => true,
                'closed_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }
}

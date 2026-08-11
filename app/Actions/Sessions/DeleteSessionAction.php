<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteSessionAction
{
    public function execute(
        Session $session,
    ): void {
        DB::transaction(function () use (
            $session,
        ): void {
            if ($session->isActive()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une session active ne peut pas être supprimée.'
                    ),
                ]);
            }

            if ($session->isClosed()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une session fermée ne peut pas être supprimée.'
                    ),
                ]);
            }

            if ($session->draw()->exists()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une session ayant déjà un tirage ne peut pas être supprimée.'
                    ),
                ]);
            }

            if ($session->participants()->exists()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Retirez les participants avant de supprimer cette session.'
                    ),
                ]);
            }

            $session->delete();
        });
    }
}

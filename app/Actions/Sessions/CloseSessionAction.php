<?php

namespace App\Actions\Sessions;

use App\Enums\SessionStatus;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CloseSessionAction
{
    public function execute(
        Session $session,
    ): Session {
        return DB::transaction(function () use (
            $session,
        ): Session {
            if ($session->isClosed()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Cette session est déjà fermée.'
                    ),
                ]);
            }

            if (! $session->isActive()) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Seule une session active peut être fermée.'
                    ),
                ]);
            }

            $this->ensureSessionCanBeClosed($session);

            $session->forceFill([
                'status' => SessionStatus::Closed,
                'closed_at' => now(),
            ]);

            $session->save();

            return $session->refresh();
        });
    }

    private function ensureSessionCanBeClosed(
        Session $session,
    ): void {
        //
        // Plus tard :
        //
        // - cotisations dues entièrement traitées ?
        // - prêts en cours ?
        // - remboursements ouverts ?
        // - tirage confirmé ?
        // - transactions en attente ?
        //
    }
}

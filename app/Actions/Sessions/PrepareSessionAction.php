<?php

namespace App\Actions\Sessions;

use App\Enums\SessionStatus;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PrepareSessionAction
{
    public function execute(Session $session): Session
    {
        return DB::transaction(function () use ($session): Session {
            $session = Session::query()->lockForUpdate()->findOrFail($session->id);

            if ($session->status !== SessionStatus::Active) {
                throw ValidationException::withMessages([
                    'session' => __('Seule une session active peut être remise en préparation.'),
                ]);
            }

            $hasOperationalHistory = $session->draw()
                ->withTrashed()
                ->whereNotNull('confirmed_at')
                ->exists()
                || $session->transactions()->exists()
                || $session->meetings()
                    ->whereNotNull('opened_at')
                    ->exists();

            if ($hasOperationalHistory) {
                throw ValidationException::withMessages([
                    'session' => __('Une session ayant déjà un historique opérationnel ou financier ne peut plus être remise en préparation.'),
                ]);
            }

            $session->forceFill([
                'status' => SessionStatus::Draft,
                'activated_at' => null,
                'closed_at' => null,
            ])->save();

            return $session->refresh();
        });
    }
}

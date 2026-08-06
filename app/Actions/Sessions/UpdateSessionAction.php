<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSessionAction
{
    /**
     * @param array{
     *     name: string,
     *     description?: string|null,
     *     start_at: string,
     *     end_at?: string|null
     * } $attributes
     */
    public function execute(
        Session $session,
        array $attributes,
    ): Session {
        return DB::transaction(function () use (
            $session,
            $attributes,
        ): Session {
            if ($session->is_closed) {
                throw ValidationException::withMessages([
                    'session' => __(
                        'Une session fermée ne peut plus être modifiée.'
                    ),
                ]);
            }

            $session->update([
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'start_at' => $attributes['start_at'],
                'end_at' => $attributes['end_at'] ?? null,
            ]);

            return $session->refresh();
        });
    }
}

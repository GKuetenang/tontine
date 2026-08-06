<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use App\Models\Tontine;
use App\Support\UniqueSlug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateSessionAction
{

    public function __construct(
        private readonly UniqueSlug $uniqueSlug
    ) {}

    /**
     * @param array{
     *     name: string,
     *     description?: string|null,
     *     start_at: string,
     *     end_at?: string|null
     * } $attributes
     */
    public function execute(
        Tontine $tontine,
        array $attributes,
    ): Session {
        return DB::transaction(function () use (
            $tontine,
            $attributes,
        ): Session {
            $session = new Session();

            $session->tontine()->associate($tontine);

            $session->fill([
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'start_at' => $attributes['start_at'] ?? null,
                'end_at' => $attributes['end_at'] ?? null,
            ]);

            $session->slug = $this->uniqueSlug->generate(
                query: $tontine->sessions()->getQuery(),
                value: $attributes['name'],
            );

            $session->save();

            return $session;
        });
    }
}

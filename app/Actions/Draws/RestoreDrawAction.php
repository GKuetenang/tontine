<?php

namespace App\Actions\Draws;

use App\Models\Draw;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RestoreDrawAction
{
    public function execute(Draw $draw): Draw
    {
        return DB::transaction(function () use ($draw): Draw {
            if (! $draw->trashed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Ce tirage n’est pas supprimé.'
                    ),
                ]);
            }

            $draw->restore();

            return $draw->refresh();
        });
    }
}

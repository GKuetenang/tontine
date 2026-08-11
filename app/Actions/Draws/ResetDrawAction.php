<?php

namespace App\Actions\Draws;

use App\Models\Draw;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ResetDrawAction
{
    public function execute(Draw $draw): Draw
    {
        return DB::transaction(function () use ($draw): Draw {
            if ($draw->isConfirmed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Un tirage confirmé ne peut pas être réinitialisé.'
                    ),
                ]);
            }

            $draw->entries()->delete();

            return $draw->refresh();
        });
    }
}

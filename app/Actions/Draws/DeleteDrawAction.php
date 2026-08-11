<?php

namespace App\Actions\Draws;

use App\Models\Draw;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteDrawAction
{
    public function execute(Draw $draw): void
    {
        DB::transaction(function () use ($draw): void {
            if ($draw->isConfirmed()) {
                throw ValidationException::withMessages([
                    'draw' => __(
                        'Un tirage confirmé ne peut pas être supprimé.'
                    ),
                ]);
            }

            $draw->entries()->delete();

            $draw->delete();
        });
    }
}

<?php

namespace App\Actions\Tontines;

use App\Data\TontineData;
use App\Models\Tontine;
use Illuminate\Support\Facades\DB;

final class UpdateTontineAction
{
    public function execute(
        Tontine $tontine,
        TontineData $data,
    ): Tontine {
        $fillable = (new Tontine)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();

        return DB::transaction(function () use (
            $tontine,
            $fillableData,
        ): Tontine {

            $tontine->fill($fillableData);

            $tontine->save();

            return $tontine->refresh();
        });
    }
}

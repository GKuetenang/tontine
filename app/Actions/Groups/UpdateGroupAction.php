<?php

namespace App\Actions\Groups;

use App\Data\GroupData;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

final class UpdateGroupAction
{
    public function execute(
        Group $group,
        GroupData $data,
    ): Group {
        $fillable = (new Group)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();

        return DB::transaction(function () use (
            $group,
            $fillableData,
        ): Group {

            $group->fill($fillableData);

            $group->save();

            return $group->refresh();
        });
    }
}

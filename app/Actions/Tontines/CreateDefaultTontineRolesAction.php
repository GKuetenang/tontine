<?php

namespace App\Actions\Tontines;

use App\Enums\TontineRole;
use App\Models\Tontine;
use Spatie\Permission\Models\Role;

class CreateDefaultTontineRolesAction
{

    public function execute(Tontine $tontine): array
    {
        $priviousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

            return array_map(
                fn(TontineRole $role): Role => Role::firstOrCreate([
                    'name' => $role->value,
                    'guard_name' => 'web',
                    'tontine_id' => $tontine->id,
                ]),
                TontineRole::cases()
            );
        } finally {
            setPermissionsTeamId($priviousTeamId);
        }
    }
}

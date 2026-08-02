<?php

namespace App\Actions\Tontines;

use App\Actions\Memberships\CreateMembershipAction;
use App\Data\TontineData;
use App\Enums\TontineRole;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTontineAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private CreateDefaultTontineRolesAction $createRoles,
        private CreateMembershipAction $createMembershipAction,
    ) {}

    public function execute(TontineData $data, User $user): Tontine
    {
        $fillable = (new Tontine)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();

        return DB::transaction(function () use ($fillableData, $user) {
            $tontine = $user->ownedTontines()->create($fillableData);

            $this->createRoles->execute($tontine);

            $this->createMembershipAction->execute(
                tontine: $tontine,
                user: $user,
                roleName: TontineRole::President->value,
            );

            return $tontine;
        });
    }
}

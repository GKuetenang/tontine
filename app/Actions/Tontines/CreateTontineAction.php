<?php

namespace App\Actions\Tontines;

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Penalties\CreateDefaultPenaltyRulesAction;
use App\Data\TontineData;
use App\Enums\TontineRole;
use App\Models\Tontine;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateTontineAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private CreateDefaultTontineRolesAction $createRoles,
        private CreateMembershipAction $createMembershipAction,
        private UniqueSlug $uniqueSlug,
        private CreateDefaultPenaltyRulesAction $createPenaltyRules,
    ) {}

    public function execute(TontineData $data, User $owner): Tontine
    {
        $fillable = (new Tontine)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();

        return DB::transaction(function () use ($fillableData, $owner) {
            $tontine = new Tontine;

            $tontine->owner()->associate($owner);

            $tontine->fill($fillableData);

            $tontine->slug = $this->uniqueSlug->generate(
                query: Tontine::query(),
                value: $fillableData['name'],
            );

            $tontine->save();

            $this->createRoles->execute($tontine);
            $this->createPenaltyRules->execute($tontine);

            $this->createMembershipAction->execute(
                tontine: $tontine,
                user: $owner,
                roleName: TontineRole::President->value,
            );

            return $tontine->refresh();
        });
    }
}

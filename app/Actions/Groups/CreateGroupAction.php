<?php

namespace App\Actions\Groups;

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Penalties\CreateDefaultPenaltyRulesAction;
use App\Data\GroupData;
use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\User;
use App\Support\UniqueSlug;
use Illuminate\Support\Facades\DB;

class CreateGroupAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private CreateDefaultGroupRolesAction $createRoles,
        private CreateMembershipAction $createMembershipAction,
        private UniqueSlug $uniqueSlug,
        private CreateDefaultPenaltyRulesAction $createPenaltyRules,
    ) {}

    public function execute(GroupData $data, User $owner): Group
    {
        $fillable = (new Group)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();

        return DB::transaction(function () use ($fillableData, $owner) {
            $group = new Group;

            $group->owner()->associate($owner);

            $group->fill($fillableData);

            $group->slug = $this->uniqueSlug->generate(
                query: Group::query(),
                value: $fillableData['name'],
            );

            $group->save();

            $this->createRoles->execute($group);
            $this->createPenaltyRules->execute($group);

            $this->createMembershipAction->execute(
                group: $group,
                user: $owner,
                roleName: GroupRole::President->value,
            );

            return $group->refresh();
        });
    }
}

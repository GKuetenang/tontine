<?php

namespace App\Actions\Groups;

use App\Actions\Mandates\ActivateMandateAction;
use App\Actions\Mandates\SaveMandateAction;
use App\Actions\Mandates\SaveMandateRoleAssignmentAction;
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
        private SaveMandateAction $saveMandate,
        private SaveMandateRoleAssignmentAction $saveMandateAssignment,
        private ActivateMandateAction $activateMandate,
    ) {}

    public function execute(GroupData $data, User $owner): Group
    {
        $fillable = (new Group)->getFillable();
        $fillableData = $data->only(...$fillable)->toArray();
        $mandateName = $data->initial_mandate_name;
        $mandateStartsAt = $data->initial_mandate_starts_at;
        $mandateEndsAt = $data->initial_mandate_ends_at;

        throw_unless(
            is_string($mandateName) && is_string($mandateStartsAt) && is_string($mandateEndsAt),
            \InvalidArgumentException::class,
            'Les informations du premier mandat sont obligatoires.',
        );

        return DB::transaction(function () use ($fillableData, $mandateEndsAt, $mandateName, $mandateStartsAt, $owner) {
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

            $membership = $this->createMembershipAction->execute(
                group: $group,
                user: $owner,
                roleName: GroupRole::Member->value,
            );

            $mandate = $this->saveMandate->execute($group, $owner, [
                'name' => $mandateName,
                'starts_at' => $mandateStartsAt,
                'ends_at' => $mandateEndsAt,
            ]);
            $administratorRole = $group->roles()->where('name', GroupRole::Administrator->value)->sole();
            $this->saveMandateAssignment->execute(
                mandate: $mandate,
                membership: $membership,
                role: $administratorRole,
                appointer: $owner,
                startsAt: $mandateStartsAt,
                endsAt: $mandateEndsAt,
            );
            $this->activateMandate->execute($mandate);

            return $group->refresh();
        });
    }
}

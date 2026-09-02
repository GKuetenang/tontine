<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMembershipAction
{
    public function __construct(
        private readonly UpdateMembershipAction $updateMembership,
    ) {}

    public function execute(
        Group $group,
        User $user,
        string $roleName,
        ?User $invitedBy = null,
        MembershipStatus $status = MembershipStatus::Active,
    ): Membership {
        return DB::transaction(function () use (
            $group,
            $user,
            $roleName,
            $invitedBy,
            $status,
        ): Membership {
            $lockedGroup = Group::query()
                ->lockForUpdate()
                ->findOrFail($group->id);

            $existingMembership = Membership::query()
                ->withTrashed()
                ->where('group_id', $lockedGroup->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existingMembership) {
                throw ValidationException::withMessages([
                    'user_id' => $existingMembership->trashed()
                        ? __(
                            'Cet utilisateur possède déjà une ancienne adhésion. Réactivez-la plutôt que d’en créer une nouvelle.'
                        )
                        : __(
                            'Cet utilisateur est déjà membre de cette réunion.'
                        ),
                ]);
            }

            return $this->createNewMembership(
                group: $lockedGroup,
                user: $user,
                roleName: $roleName,
                invitedBy: $invitedBy,
                status: $status,
            );
        }, attempts: 3);
    }

    private function createNewMembership(
        Group $group,
        User $user,
        string $roleName,
        ?User $invitedBy,
        MembershipStatus $status,
    ): Membership {
        $membership = new Membership;

        $membership->user()->associate($user);
        $membership->group()->associate($group);

        if ($invitedBy !== null) {
            $membership->inviter()->associate($invitedBy);
        }

        $membership->forceFill([
            'member_number' => $this->generateMemberNumber($group),
            'status' => $status,
        ]);

        $membership->save();

        $this->assignRole(
            group: $group,
            user: $user,
            roleName: $roleName,
        );

        return $membership;
    }

    private function assignRole(
        Group $group,
        User $user,
        string $roleName,
    ): void {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($group->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->assignRole($roleName);
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function generateMemberNumber(Group $group): string
    {
        $prefix = $group->member_number_prefix
            ?: config('memberships.default_member_number_prefix', 'MEM');
        $memberNumber = sprintf(
            '%s-%06d',
            strtoupper($prefix),
            $group->next_member_number,
        );

        $group->increment('next_member_number');

        return $memberNumber;
    }
}

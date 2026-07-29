<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateMembershipAction
{
    public function execute(
        Tontine $tontine,
        User $user,
        string $roleName,
        ?User $invitedBy = null,
        MembershipStatus $status = MembershipStatus::Active,
    ): Membership {
        return DB::transaction(function () use (
            $tontine,
            $user,
            $roleName,
            $invitedBy,
            $status,
        ): Membership {
            /*
             * Lock the tontine record to prevent race conditions 
             * when creating memberships and generating member numbers.
             * 
             * This ensures that two concurrent requests 
             * cannot create memberships for the same tontine at the same
             */
            $lockedTontine = Tontine::query()
                ->lockForUpdate()
                ->findOrFail($tontine->getKey());

            $existingMembership = Membership::query()
                ->withTrashed()
                ->where('tontine_id', $lockedTontine->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingMembership !== null) {
                throw ValidationException::withMessages([
                    'user_id' => __('Cet utilisateur appartient déjà à cette tontine.'),
                ]);
            }

            $memberNumber = $this->generateMemberNumber(
                $lockedTontine
            );

            $membership = new Membership();

            $membership->user()->associate($user);
            $membership->tontine()->associate($lockedTontine);

            if ($invitedBy !== null) {
                $membership->inviter()->associate($invitedBy);
            }

            $membership->fill([
                'member_number' => $memberNumber,
                'status' => $status,
            ]);

            $membership->save();

            $this->assignRole(
                tontine: $lockedTontine,
                user: $user,
                roleName: $roleName,
            );

            return $membership;
        }, attempts: 3);
    }

    private function assignRole(
        Tontine $tontine,
        User $user,
        string $roleName,
    ): void {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->where('tontine_id', $tontine->id)
                ->first();

            if ($role === null) {
                throw ValidationException::withMessages([
                    'role' => __('Le rôle sélectionné n’existe pas dans cette tontine.'),
                ]);
            }

            $user->assignRole($role);
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function generateMemberNumber(
        Tontine $tontine
    ): string {

        $nextNumber = $tontine->next_member_number;
        $prefix = $tontine->member_number_prefix ?? config('memberships.default_member_number_prefix', 'MEM');

        $memberNumber = sprintf(
            '%s-%06d',
            $prefix,
            $nextNumber
        );

        $tontine->increment('next_member_number');

        return $memberNumber;
    }
}

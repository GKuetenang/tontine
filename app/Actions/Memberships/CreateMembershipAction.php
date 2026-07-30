<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateMembershipAction
{
    public function __construct(
        private readonly UpdateMembershipAction $updateMembership,
    ) {}

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
            $lockedTontine = Tontine::query()
                ->lockForUpdate()
                ->findOrFail($tontine->id);

            $existingMembership = Membership::query()
                ->withTrashed()
                ->where('tontine_id', $lockedTontine->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existingMembership !== null) {
                return $this->handleExistingMembership(
                    membership: $existingMembership,
                    tontine: $lockedTontine,
                    user: $user,
                    roleName: $roleName,
                    invitedBy: $invitedBy,
                    status: $status,
                );
            }

            return $this->createNewMembership(
                tontine: $lockedTontine,
                user: $user,
                roleName: $roleName,
                invitedBy: $invitedBy,
                status: $status,
            );
        }, attempts: 3);
    }

    private function handleExistingMembership(
        Membership $membership,
        Tontine $tontine,
        User $user,
        string $roleName,
        ?User $invitedBy,
        MembershipStatus $status,
    ): Membership {
        if (! $membership->trashed()) {
            throw ValidationException::withMessages([
                'user_id' => __(
                    'Cet utilisateur appartient déjà à cette tontine.'
                ),
            ]);
        }

        return $this->updateMembership->execute(
            membership: $membership,
            tontine: $tontine,
            user: $user,
            roleName: $roleName,
            invitedBy: $invitedBy,
            status: $status,
        );
    }

    private function createNewMembership(
        Tontine $tontine,
        User $user,
        string $roleName,
        ?User $invitedBy,
        MembershipStatus $status,
    ): Membership {
        $membership = new Membership();

        $membership->user()->associate($user);
        $membership->tontine()->associate($tontine);

        if ($invitedBy !== null) {
            $membership->inviter()->associate($invitedBy);
        }

        $membership->forceFill([
            'member_number' => $this->generateMemberNumber($tontine),
            'status' => $status,
        ]);

        $membership->save();

        $this->assignRole(
            tontine: $tontine,
            user: $user,
            roleName: $roleName,
        );

        return $membership;
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

            $user->assignRole($roleName);
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function generateMemberNumber(Tontine $tontine): string
    {
        $prefix = $tontine->member_number_prefix
            ?: config('memberships.default_member_number_prefix', 'MEM');
        $memberNumber = sprintf(
            '%s-%06d',
            strtoupper($prefix),
            $tontine->next_member_number,
        );

        $tontine->increment('next_member_number');

        return $memberNumber;
    }
}

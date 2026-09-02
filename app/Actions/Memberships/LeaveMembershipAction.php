<?php

namespace App\Actions\Memberships;

use App\Enums\GroupRole;
use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LeaveMembershipAction
{
    public function execute(
        Membership $membership,
    ): void {
        DB::transaction(function () use ($membership): void {
            $membership->loadMissing([
                'user',
                'group',
            ]);

            $this->ensureMembershipCanLeave($membership);
            $this->ensureNotLastPresident($membership);
            $this->removeTeamRoles($membership);

            $membership->forceFill([
                'status' => MembershipStatus::Left,
                'left_at' => now(),
            ]);

            $membership->save();

            $membership->delete();
        });
    }

    private function ensureMembershipCanLeave(
        Membership $membership,
    ): void {
        if ($membership->trashed()) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Ce membre a déjà quitté la réunion.'
                ),
            ]);
        }

        if ($membership->status === MembershipStatus::Left) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Ce membre a déjà quitté la réunion.'
                ),
            ]);
        }
    }

    private function ensureNotLastPresident(
        Membership $membership,
    ): void {
        $user = $membership->user;

        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $membership->group_id,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            if (
                ! $user->hasRole(
                    GroupRole::President->value,
                )
            ) {
                return;
            }

            $presidentsCount = User::role(
                GroupRole::President->value,
            )->count();

            if ($presidentsCount <= 1) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Le dernier président de la réunion ne peut pas quitter la réunion.'
                    ),
                ]);
            }
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }

    private function removeTeamRoles(
        Membership $membership,
    ): void {
        $user = $membership->user;

        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId(
                $membership->group_id,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            $user->syncRoles([]);
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

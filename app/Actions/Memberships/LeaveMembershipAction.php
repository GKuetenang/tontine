<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Enums\TontineRole;
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
                'tontine',
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
                    'Ce membre a déjà quitté la tontine.'
                ),
            ]);
        }

        if ($membership->status === MembershipStatus::Left) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Ce membre a déjà quitté la tontine.'
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
                $membership->tontine_id,
            );

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            if (
                ! $user->hasRole(
                    TontineRole::President->value,
                )
            ) {
                return;
            }

            $presidentsCount = User::role(
                TontineRole::President->value,
            )->count();

            if ($presidentsCount <= 1) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Le dernier président de la tontine ne peut pas quitter la tontine.'
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
                $membership->tontine_id,
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

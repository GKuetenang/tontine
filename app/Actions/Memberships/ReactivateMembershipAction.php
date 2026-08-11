<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReactivateMembershipAction
{
    public function execute(
        Membership $membership,
        string $roleName,
    ): Membership {
        return DB::transaction(function () use (
            $membership,
            $roleName,
        ): Membership {
            if (! $membership->trashed()) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Ce membre est déjà actif.'
                    ),
                ]);
            }

            $membership->restore();

            $membership->fill([
                'status' => MembershipStatus::Active,
                'left_at' => null,
            ]);

            $membership->save();

            $previousTeamId = getPermissionsTeamId();

            try {
                setPermissionsTeamId(
                    $membership->tontine_id,
                );

                $membership->user
                    ->unsetRelation('roles');

                $membership->user
                    ->assignRole($roleName);
            } finally {
                setPermissionsTeamId($previousTeamId);

                $membership->user
                    ->unsetRelation('roles');

                $membership->user
                    ->unsetRelation('permissions');
            }

            return $membership->refresh();
        });
    }
}

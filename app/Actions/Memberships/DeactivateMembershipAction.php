<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeactivateMembershipAction
{
    public function __construct(
        private readonly IsLastPresidentAction $isLastPresident,
    ) {}

    public function execute(Membership $membership): Membership
    {
        return DB::transaction(function () use (
            $membership
        ): Membership {
            $membership->loadMissing(['user', 'tontine']);

            if ($this->isLastPresident->execute($membership)) {
                throw ValidationException::withMessages([
                    'membership' => __(
                        'Impossible de retirer le dernier président de la tontine.'
                    ),
                ]);
            }

            $previousTeamId = getPermissionsTeamId();

            try {
                setPermissionsTeamId($membership->tontine_id);

                $membership->user->unsetRelation('roles');
                $membership->user->unsetRelation('permissions');

                $membership->user->syncRoles([]);
            } finally {
                setPermissionsTeamId($previousTeamId);

                $membership->user->unsetRelation('roles');
                $membership->user->unsetRelation('permissions');
            }

            $membership->fill([
                'status' => MembershipStatus::Inactive,
                'left_at' => now()
            ])->save();

            $membership->delete();

            return $membership;
        }, attempts: 3);
    }
}

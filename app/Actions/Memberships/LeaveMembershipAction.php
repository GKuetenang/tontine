<?php

namespace App\Actions\Memberships;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LeaveMembershipAction
{
    public function __construct(
        private IsLastGroupAdministratorAction $isLastAdministrator,
    ) {}

    public function execute(
        Membership $membership,
    ): void {
        DB::transaction(function () use ($membership): void {
            $membership->loadMissing([
                'user',
                'group',
            ]);

            $this->ensureMembershipCanLeave($membership);
            $this->ensureNotLastAdministrator($membership);
            $this->endCurrentMandateAssignments($membership);
            $this->removeTeamRoles($membership);

            $membership->forceFill([
                'status' => MembershipStatus::Left,
                'left_at' => now(),
            ]);

            $membership->save();

            $membership->delete();
        });
    }

    private function endCurrentMandateAssignments(Membership $membership): void
    {
        $membership->mandateRoleAssignments()
            ->whereHas('mandate', fn ($query) => $query->where('status', 'active'))
            ->whereDate('starts_at', '<=', today())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
            ->update([
                'ends_at' => today(),
                'ended_by' => $membership->user_id,
                'end_reason' => __('Départ volontaire de la réunion'),
                'updated_at' => now(),
            ]);
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

    private function ensureNotLastAdministrator(Membership $membership): void
    {
        if ($this->isLastAdministrator->execute($membership)) {
            throw ValidationException::withMessages([
                'membership' => __(
                    'Le dernier président ou administrateur de la réunion ne peut pas la quitter.'
                ),
            ]);
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

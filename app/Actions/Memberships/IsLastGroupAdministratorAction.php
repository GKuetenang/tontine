<?php

namespace App\Actions\Memberships;

use App\Enums\GroupRole;
use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Query\Builder;

final class IsLastGroupAdministratorAction
{
    public function execute(Membership $membership): bool
    {
        if (! $membership->isActive() || ! $this->membershipCanAdminister($membership)) {
            return false;
        }

        return $this->activeAdministratorsCount($membership) <= 1;
    }

    private function membershipCanAdminister(Membership $membership): bool
    {
        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($membership->group_id);
            $membership->user->unsetRelation('roles');
            $membership->user->unsetRelation('permissions');

            return $membership->user->hasAnyRole([
                GroupRole::President->value,
                GroupRole::Administrator->value,
            ]);
        } finally {
            setPermissionsTeamId($previousTeamId);
            $membership->user->unsetRelation('roles');
            $membership->user->unsetRelation('permissions');
        }
    }

    private function activeAdministratorsCount(Membership $membership): int
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelHasRoles = $tableNames['model_has_roles'];
        $roles = $tableNames['roles'];
        $modelMorphKey = $columnNames['model_morph_key'];
        $teamForeignKey = $columnNames['team_foreign_key'];

        return Membership::query()
            ->where('memberships.group_id', $membership->group_id)
            ->where('memberships.status', MembershipStatus::Active)
            ->whereNull('memberships.left_at')
            ->whereNull('memberships.deleted_at')
            ->whereExists(function (Builder $query) use ($membership, $modelHasRoles, $roles, $modelMorphKey, $teamForeignKey): void {
                $query->selectRaw('1')
                    ->from("{$modelHasRoles} as mhr")
                    ->join("{$roles} as r", 'r.id', '=', 'mhr.role_id')
                    ->whereColumn("mhr.{$modelMorphKey}", 'memberships.user_id')
                    ->where('mhr.model_type', (new User)->getMorphClass())
                    ->where("mhr.{$teamForeignKey}", $membership->group_id)
                    ->whereIn('r.name', [
                        GroupRole::President->value,
                        GroupRole::Administrator->value,
                    ])
                    ->where('r.guard_name', 'web');
            })
            ->count();
    }
}

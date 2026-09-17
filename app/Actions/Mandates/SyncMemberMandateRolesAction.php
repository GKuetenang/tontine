<?php

namespace App\Actions\Mandates;

use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Models\Group;
use App\Models\User;

final class SyncMemberMandateRolesAction
{
    public function execute(Group $group, User $user): void
    {
        if (! $group->mandates()->where('status', '!=', MandateStatus::Draft)->exists()) {
            return;
        }

        $membership = $group->memberships()->active()->where('user_id', $user->id)->first();

        if (! $membership) {
            $user->syncRoles([]);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return;
        }

        $roleNames = $group->mandates()
            ->where('status', MandateStatus::Active)
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->whereHas('assignments', fn ($query) => $query
                ->where('membership_id', $membership->id)
                ->whereDate('starts_at', '<=', today())
                ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhereDate('ends_at', '>=', today())))
            ->with(['assignments' => fn ($query) => $query
                ->where('membership_id', $membership->id)
                ->whereDate('starts_at', '<=', today())
                ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
                ->with('role:id,name')])
            ->get()
            ->flatMap->assignments
            ->pluck('role.name')
            ->filter()
            ->prepend(GroupRole::Member->value)
            ->unique()
            ->values()
            ->all();

        $currentRoles = $user->load('roles')->roles->pluck('name')->sort()->values()->all();
        $expectedRoles = collect($roleNames)->sort()->values()->all();

        if ($currentRoles !== $expectedRoles) {
            $user->syncRoles($roleNames);
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}

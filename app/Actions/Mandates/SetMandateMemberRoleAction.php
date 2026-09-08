<?php

namespace App\Actions\Mandates;

use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Models\Mandate;
use App\Models\MandateRoleAssignment;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class SetMandateMemberRoleAction
{
    public function __construct(private SyncMemberMandateRolesAction $syncMemberRoles) {}

    public function execute(Mandate $mandate, Membership $membership, ?Role $role, User $actor): void
    {
        DB::transaction(function () use ($mandate, $membership, $role, $actor): void {
            $mandate = Mandate::query()->lockForUpdate()->findOrFail($mandate->id);

            if ($mandate->status === MandateStatus::Closed) {
                throw ValidationException::withMessages(['role_id' => __('Un mandat clôturé ne peut plus être modifié.')]);
            }

            if ($membership->group_id !== $mandate->group_id || ($role && (int) $role->group_id !== $mandate->group_id)) {
                throw ValidationException::withMessages(['role_id' => __('Le membre et le rôle doivent appartenir à cette réunion.')]);
            }

            $currentAssignments = $mandate->assignments()
                ->with(['role:id,name', 'membership.user'])
                ->where('membership_id', $membership->id)
                ->when($mandate->status === MandateStatus::Active, fn ($query) => $query
                    ->whereDate('starts_at', '<=', today())
                    ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhereDate('ends_at', '>=', today())))
                ->lockForUpdate()
                ->get();

            if ($mandate->status === MandateStatus::Active
                && $currentAssignments->contains(fn (MandateRoleAssignment $assignment): bool => $assignment->role->name === GroupRole::President->value)
                && $role?->name !== GroupRole::President->value) {
                throw ValidationException::withMessages([
                    'role_id' => __('Nommez d’abord le nouveau président depuis sa ligne.'),
                ]);
            }

            $affectedUsers = $currentAssignments->pluck('membership.user')->filter()->push($membership->user)->unique('id');

            if ($role?->name === GroupRole::President->value) {
                $otherPresidents = $mandate->assignments()
                    ->with(['role:id,name', 'membership.user'])
                    ->where('membership_id', '!=', $membership->id)
                    ->whereHas('role', fn ($query) => $query->where('name', GroupRole::President->value))
                    ->when($mandate->status === MandateStatus::Active, fn ($query) => $query
                        ->whereDate('starts_at', '<=', today())
                        ->where(fn ($dateQuery) => $dateQuery->whereNull('ends_at')->orWhereDate('ends_at', '>=', today())))
                    ->lockForUpdate()
                    ->get();
                $affectedUsers = $affectedUsers->merge($otherPresidents->pluck('membership.user'))->filter()->unique('id');
                $currentAssignments = $currentAssignments->merge($otherPresidents);
            }

            $alreadyAssigned = $currentAssignments->count() === 1
                && $currentAssignments->first()->membership_id === $membership->id
                && $currentAssignments->first()->role_id === $role?->id;

            if (! $alreadyAssigned) {
                foreach ($currentAssignments as $assignment) {
                    $this->endAssignment($assignment, $mandate, $actor);
                }

                if ($role) {
                    $mandate->assignments()->create([
                        'membership_id' => $membership->id,
                        'role_id' => $role->id,
                        'starts_at' => $mandate->status === MandateStatus::Draft ? $mandate->starts_at : today(),
                        'ends_at' => $mandate->ends_at,
                        'appointed_by' => $actor->id,
                    ]);
                }
            }

            if ($mandate->status === MandateStatus::Active) {
                $previousTeamId = getPermissionsTeamId();
                try {
                    setPermissionsTeamId($mandate->group_id);
                    foreach ($affectedUsers as $user) {
                        $user->unsetRelation('roles');
                        $user->unsetRelation('permissions');
                        $this->syncMemberRoles->execute($mandate->group, $user);
                    }
                } finally {
                    setPermissionsTeamId($previousTeamId);
                }
            }
        });
    }

    private function endAssignment(MandateRoleAssignment $assignment, Mandate $mandate, User $actor): void
    {
        if ($mandate->status === MandateStatus::Draft || $assignment->starts_at->gte(today())) {
            $assignment->delete();

            return;
        }

        $assignment->forceFill([
            'ends_at' => today()->subDay(),
            'ended_by' => $actor->id,
            'end_reason' => __('Changement de responsabilité'),
        ])->save();
    }
}

<?php

namespace App\Actions\Mandates;

use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Models\Mandate;
use App\Models\MandateRoleAssignment;
use App\Models\Membership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class SaveMandateRoleAssignmentAction
{
    public function __construct(private SyncMemberMandateRolesAction $syncMemberRoles) {}

    public function execute(
        Mandate $mandate,
        Membership $membership,
        Role $role,
        User $appointer,
        string $startsAt,
        ?string $endsAt,
    ): MandateRoleAssignment {
        return DB::transaction(function () use ($mandate, $membership, $role, $appointer, $startsAt, $endsAt): MandateRoleAssignment {
            $lockedMandate = Mandate::query()->lockForUpdate()->findOrFail($mandate->id);

            if ($lockedMandate->status === MandateStatus::Closed) {
                throw ValidationException::withMessages([
                    'membership_id' => __('Un mandat clôturé ne peut plus recevoir d’affectation.'),
                ]);
            }

            if ($membership->group_id !== $lockedMandate->group_id || (int) $role->group_id !== $lockedMandate->group_id) {
                throw ValidationException::withMessages([
                    'membership_id' => __('Le membre et le rôle doivent appartenir à la même réunion que le mandat.'),
                ]);
            }

            $start = CarbonImmutable::parse($startsAt)->startOfDay();
            $end = $endsAt ? CarbonImmutable::parse($endsAt)->startOfDay() : $lockedMandate->ends_at;

            if ($start->lt($lockedMandate->starts_at) || $end->gt($lockedMandate->ends_at) || $end->lt($start)) {
                throw ValidationException::withMessages([
                    'starts_at' => __('La période d’affectation doit être comprise dans celle du mandat.'),
                ]);
            }

            if ($role->name === GroupRole::President->value && $lockedMandate->assignments()
                ->where('role_id', $role->id)
                ->whereDate('starts_at', '<=', $end)
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $start))
                ->exists()) {
                throw ValidationException::withMessages([
                    'role_id' => __('Une présidence est déjà définie sur cette période.'),
                ]);
            }

            $assignment = $lockedMandate->assignments()->create([
                'membership_id' => $membership->id,
                'role_id' => $role->id,
                'starts_at' => $start,
                'ends_at' => $end,
                'appointed_by' => $appointer->id,
            ]);

            if ($lockedMandate->status === MandateStatus::Active && $start->lte(today()) && $end->gte(today())) {
                $previousTeamId = getPermissionsTeamId();
                try {
                    setPermissionsTeamId($lockedMandate->group_id);
                    $membership->user->unsetRelation('roles');
                    $membership->user->unsetRelation('permissions');
                    $this->syncMemberRoles->execute($lockedMandate->group, $membership->user);
                } finally {
                    setPermissionsTeamId($previousTeamId);
                }
            }

            return $assignment;
        });
    }
}

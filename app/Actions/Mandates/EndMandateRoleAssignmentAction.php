<?php

namespace App\Actions\Mandates;

use App\Enums\MandateStatus;
use App\Models\MandateRoleAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EndMandateRoleAssignmentAction
{
    public function __construct(
        private SyncMemberMandateRolesAction $syncMemberRoles,
        private EnsureMandateKeepsAdministratorAction $ensureMandateKeepsAdministrator,
    ) {}

    public function execute(MandateRoleAssignment $assignment, User $actor, string $endsAt, ?string $reason): MandateRoleAssignment
    {
        return DB::transaction(function () use ($assignment, $actor, $endsAt, $reason): MandateRoleAssignment {
            $assignment = MandateRoleAssignment::query()
                ->with(['mandate.group', 'membership.user'])
                ->lockForUpdate()
                ->findOrFail($assignment->id);
            $end = CarbonImmutable::parse($endsAt)->startOfDay();

            if ($assignment->mandate->status === MandateStatus::Closed || $end->lt($assignment->starts_at) || $end->gt($assignment->mandate->ends_at)) {
                throw ValidationException::withMessages([
                    'ends_at' => __('La date de fin doit être comprise dans la période de l’affectation et du mandat.'),
                ]);
            }

            if ($this->ensureMandateKeepsAdministrator->assignmentIsAdministrator($assignment)
                && $end->lt($assignment->mandate->ends_at)) {
                $this->ensureMandateKeepsAdministrator->execute(
                    $assignment->mandate,
                    [$assignment->id],
                );
            }

            $assignment->forceFill([
                'ends_at' => $end,
                'ended_by' => $actor->id,
                'end_reason' => $reason,
            ])->save();

            $previousTeamId = getPermissionsTeamId();
            try {
                setPermissionsTeamId($assignment->mandate->group_id);
                $assignment->membership->user->unsetRelation('roles');
                $assignment->membership->user->unsetRelation('permissions');
                $this->syncMemberRoles->execute($assignment->mandate->group, $assignment->membership->user);
            } finally {
                setPermissionsTeamId($previousTeamId);
            }

            return $assignment->refresh();
        });
    }
}

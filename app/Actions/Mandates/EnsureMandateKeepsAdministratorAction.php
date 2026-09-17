<?php

namespace App\Actions\Mandates;

use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Models\Mandate;
use App\Models\MandateRoleAssignment;
use Illuminate\Validation\ValidationException;

final class EnsureMandateKeepsAdministratorAction
{
    /**
     * @param  array<int>  $ignoredAssignmentIds
     */
    public function execute(Mandate $mandate, array $ignoredAssignmentIds): void
    {
        $hasAnotherAdministrator = $mandate->assignments()
            ->whereNotIn('id', $ignoredAssignmentIds)
            ->whereHas('role', fn ($query) => $query->whereIn('name', [
                GroupRole::President->value,
                GroupRole::Administrator->value,
            ]))
            ->when($mandate->status === MandateStatus::Active, fn ($query) => $query
                ->whereDate('starts_at', '<=', today())
                ->where(fn ($dateQuery) => $dateQuery
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', today())))
            ->lockForUpdate()
            ->exists();

        if (! $hasAnotherAdministrator) {
            throw ValidationException::withMessages([
                'role_id' => __('Ce membre est le seul responsable capable d’administrer la réunion. Nommez d’abord un président ou un administrateur.'),
            ]);
        }
    }

    public function assignmentIsAdministrator(MandateRoleAssignment $assignment): bool
    {
        $assignment->loadMissing('role:id,name');

        return in_array($assignment->role->name, [
            GroupRole::President->value,
            GroupRole::Administrator->value,
        ], true);
    }
}

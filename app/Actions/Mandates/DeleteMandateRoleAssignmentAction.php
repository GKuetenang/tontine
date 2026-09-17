<?php

namespace App\Actions\Mandates;

use App\Enums\MandateStatus;
use App\Models\MandateRoleAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteMandateRoleAssignmentAction
{
    public function __construct(
        private EnsureMandateKeepsAdministratorAction $ensureMandateKeepsAdministrator,
    ) {}

    public function execute(MandateRoleAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment): void {
            $assignment = MandateRoleAssignment::query()
                ->with(['mandate', 'role:id,name'])
                ->lockForUpdate()
                ->findOrFail($assignment->id);

            if ($assignment->mandate->status !== MandateStatus::Draft) {
                throw ValidationException::withMessages([
                    'assignment' => __('Une affectation active ou historique ne peut pas être supprimée.'),
                ]);
            }

            if ($this->ensureMandateKeepsAdministrator->assignmentIsAdministrator($assignment)) {
                $this->ensureMandateKeepsAdministrator->execute(
                    $assignment->mandate,
                    [$assignment->id],
                );
            }

            $assignment->delete();
        });
    }
}

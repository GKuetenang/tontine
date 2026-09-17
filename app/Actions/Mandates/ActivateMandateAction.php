<?php

namespace App\Actions\Mandates;

use App\Enums\GroupRole;
use App\Enums\MandateStatus;
use App\Models\Mandate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ActivateMandateAction
{
    public function __construct(private SyncMemberMandateRolesAction $syncMemberRoles) {}

    public function execute(Mandate $mandate): Mandate
    {
        return DB::transaction(function () use ($mandate): Mandate {
            $lockedMandate = Mandate::query()->with('group.memberships.user')->lockForUpdate()->findOrFail($mandate->id);

            if ($lockedMandate->status !== MandateStatus::Draft) {
                throw ValidationException::withMessages(['mandate' => __('Seul un mandat en brouillon peut être activé.')]);
            }

            if (today()->lt($lockedMandate->starts_at) || today()->gt($lockedMandate->ends_at)) {
                throw ValidationException::withMessages(['mandate' => __('La date actuelle doit être comprise dans la période du mandat.')]);
            }

            $hasAdministrator = $lockedMandate->assignments()
                ->whereHas('role', fn ($query) => $query->whereIn('name', [
                    GroupRole::President->value,
                    GroupRole::Administrator->value,
                ]))
                ->whereDate('starts_at', '<=', today())
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
                ->exists();

            if (! $hasAdministrator) {
                throw ValidationException::withMessages(['mandate' => __('Le mandat doit avoir un président ou un administrateur en fonction à sa date d’activation.')]);
            }

            $lockedMandate->group->mandates()
                ->where('status', MandateStatus::Active)
                ->whereKeyNot($lockedMandate->id)
                ->update(['status' => MandateStatus::Closed, 'closed_at' => now()]);

            $lockedMandate->forceFill([
                'status' => MandateStatus::Active,
                'activated_at' => now(),
                'closed_at' => null,
            ])->save();

            $previousTeamId = getPermissionsTeamId();

            try {
                setPermissionsTeamId($lockedMandate->group_id);
                foreach ($lockedMandate->group->memberships as $membership) {
                    $membership->user->unsetRelation('roles');
                    $membership->user->unsetRelation('permissions');
                    $this->syncMemberRoles->execute($lockedMandate->group, $membership->user);
                }
            } finally {
                setPermissionsTeamId($previousTeamId);
            }

            return $lockedMandate->refresh();
        });
    }
}

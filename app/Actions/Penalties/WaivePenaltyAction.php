<?php

namespace App\Actions\Penalties;

use App\Enums\PenaltyStatus;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class WaivePenaltyAction
{
    public function execute(Penalty $penalty, User $user, string $reason): Penalty
    {
        return DB::transaction(function () use ($penalty, $user, $reason): Penalty {
            $penalty = Penalty::query()->lockForUpdate()->findOrFail($penalty->id);
            if ($penalty->status !== PenaltyStatus::Pending) {
                throw ValidationException::withMessages(['penalty' => __('Seule une pénalité à payer peut être exemptée.')]);
            }
            $penalty->forceFill([
                'status' => PenaltyStatus::Waived,
                'waived_at' => now(),
                'waived_by' => $user->id,
                'waiver_reason' => $reason,
            ])->save();

            return $penalty->refresh();
        });
    }
}

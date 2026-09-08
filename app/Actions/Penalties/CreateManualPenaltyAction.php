<?php

namespace App\Actions\Penalties;

use App\Enums\PenaltyStatus;
use App\Models\Meeting;
use App\Models\Membership;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateManualPenaltyAction
{
    public function execute(Session $session, Meeting $meeting, Membership $membership, PenaltyRule $rule, User $creator, string $amount, string $reason): Penalty
    {
        return DB::transaction(function () use ($session, $meeting, $membership, $rule, $creator, $amount, $reason): Penalty {
            if ($meeting->session_id !== $session->id || $membership->group_id !== $session->group_id || $rule->group_id !== $session->group_id) {
                throw ValidationException::withMessages(['penalty' => __('Les éléments sélectionnés ne correspondent pas à cette session.')]);
            }

            if (! $session->participants()->where('membership_id', $membership->id)->exists()) {
                throw ValidationException::withMessages(['membership_id' => __('Ce membre ne participe pas à cette session.')]);
            }

            return Penalty::query()->create([
                'penalty_rule_id' => $rule->id,
                'meeting_id' => $meeting->id,
                'membership_id' => $membership->id,
                'trigger' => $rule->trigger,
                'amount' => $amount,
                'rule_name' => $rule->name,
                'source' => 'manual',
                'status' => PenaltyStatus::Pending,
                'reason' => $reason,
                'assessed_at' => now(),
                'created_by' => $creator->id,
            ]);
        });
    }
}

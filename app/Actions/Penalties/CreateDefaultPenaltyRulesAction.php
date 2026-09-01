<?php

namespace App\Actions\Penalties;

use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use App\Models\Tontine;

final class CreateDefaultPenaltyRulesAction
{
    public function execute(Tontine $tontine): void
    {
        foreach ($this->defaults() as $rule) {
            $tontine->penaltyRules()->firstOrCreate(
                ['code' => $rule['code']],
                $rule,
            );
        }
    }

    private function defaults(): array
    {
        return [
            [
                'code' => PenaltyTrigger::MeetingLate->value,
                'name' => PenaltyTrigger::MeetingLate->label(),
                'trigger' => PenaltyTrigger::MeetingLate,
                'calculation_type' => PenaltyCalculationType::Fixed,
                'grace_period' => 15,
                'grace_unit' => PenaltyGraceUnit::Minutes,
                'is_automatic' => true,
                'is_active' => false,
            ],
            [
                'code' => PenaltyTrigger::MeetingAbsent->value,
                'name' => PenaltyTrigger::MeetingAbsent->label(),
                'trigger' => PenaltyTrigger::MeetingAbsent,
                'calculation_type' => PenaltyCalculationType::Fixed,
                'is_automatic' => true,
                'is_active' => false,
            ],
            [
                'code' => PenaltyTrigger::ContributionLate->value,
                'name' => PenaltyTrigger::ContributionLate->label(),
                'trigger' => PenaltyTrigger::ContributionLate,
                'calculation_type' => PenaltyCalculationType::Fixed,
                'grace_period' => 1,
                'grace_unit' => PenaltyGraceUnit::Days,
                'is_automatic' => true,
                'is_active' => false,
            ],
        ];
    }
}

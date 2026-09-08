<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\Membership;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Penalty> */
class PenaltyFactory extends Factory
{
    protected $model = Penalty::class;

    public function definition(): array
    {
        return [
            'penalty_rule_id' => PenaltyRule::factory(),
            'meeting_id' => Meeting::factory(),
            'membership_id' => Membership::factory(),
            'contribution_id' => null,
            'trigger' => 'meeting_absent',
            'amount' => '1000.00',
            'rule_name' => 'Absence à une assise',
            'source' => 'automatic',
            'status' => 'pending',
            'assessed_at' => now(),
        ];
    }
}

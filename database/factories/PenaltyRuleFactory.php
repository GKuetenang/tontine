<?php

namespace Database\Factories;

use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyTrigger;
use App\Models\Group;
use App\Models\PenaltyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PenaltyRule> */
class PenaltyRuleFactory extends Factory
{
    protected $model = PenaltyRule::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'code' => fake()->unique()->slug(2),
            'name' => fake()->sentence(3),
            'trigger' => PenaltyTrigger::MeetingAbsent,
            'calculation_type' => PenaltyCalculationType::Fixed,
            'value' => '1000.00',
            'grace_period' => null,
            'grace_unit' => null,
            'is_automatic' => true,
            'is_active' => true,
        ];
    }
}

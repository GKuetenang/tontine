<?php

namespace Database\Factories;

use App\Enums\MandateStatus;
use App\Models\Group;
use App\Models\Mandate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mandate> */
class MandateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'name' => 'Mandat '.$this->faker->year(),
            'starts_at' => today()->startOfYear(),
            'ends_at' => today()->endOfYear(),
            'status' => MandateStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => MandateStatus::Active,
            'activated_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\MandateRoleAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MandateRoleAssignment> */
class MandateRoleAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'starts_at' => today()->startOfYear(),
            'ends_at' => today()->endOfYear(),
            'appointed_by' => User::factory(),
        ];
    }
}

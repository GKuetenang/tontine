<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tontine_id' => Tontine::factory(),
            'member_number' => fake()->unique()->numerify('MEM-######'),
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
            'verified_at' => now(),
            'left_at' => null,
            'invited_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Active,
            'left_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => MembershipStatus::Inactive,
            'left_at' => now(),
        ]);
    }
}

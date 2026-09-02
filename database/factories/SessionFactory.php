<?php

namespace Database\Factories;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Group;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Session>
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween(
            '-1 month',
            '+3 months',
        );

        $year = fake()->unique()->numberBetween(
            2020,
            2040,
        );

        $name = "Session {$year}";

        return [
            'group_id' => Group::factory(),

            'name' => $name,

            'slug' => Str::slug($name)
                .'-'
                .Str::lower(Str::random(8)),

            'description' => fake()->optional()->sentence(),

            'default_contribution_amount' => 50_000,

            'base_contribution_amount' => null,

            'draw_allocation_mode' => DrawAllocationMode::OnePerMember,

            'start_at' => $startAt,

            'end_at' => fake()->dateTimeBetween(
                $startAt,
                '+12 months',
            ),

            'status' => SessionStatus::Draft,

            'activated_at' => null,

            'closed_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Draft,
            'activated_at' => null,
            'closed_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Active,
            'activated_at' => now(),
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => SessionStatus::Closed,
            'activated_at' => now()->subMonth(),
            'closed_at' => now(),
        ]);
    }

    public function basedOnContribution(
        int $baseAmount = 50_000,
    ): static {
        return $this->state(fn (): array => [
            'draw_allocation_mode' => DrawAllocationMode::BasedOnContribution,

            'base_contribution_amount' => $baseAmount,
        ]);
    }

    public function customDrawAllocation(): static
    {
        return $this->state(fn (): array => [
            'draw_allocation_mode' => DrawAllocationMode::Custom,

            'base_contribution_amount' => null,
        ]);
    }
}

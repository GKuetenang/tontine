<?php

namespace Database\Factories;

use App\Models\InsuranceContribution;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InsuranceContribution> */
class InsuranceContributionFactory extends Factory
{
    protected $model = InsuranceContribution::class;

    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'session_id' => fn (array $attributes) => Session::factory()->create([
                'group_id' => Membership::query()->findOrFail($attributes['membership_id'])->group_id,
            ]),
            'amount' => '1000.00',
            'description' => fake()->optional()->sentence(),
            'occurred_at' => now(),
            'created_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (InsuranceContribution $contribution): void {
            $contribution->session->participants()->firstOrCreate(
                ['membership_id' => $contribution->membership_id],
                [
                    'contribution_amount' => '50000',
                    'draw_entries_count' => 1,
                    'is_active' => true,
                    'joined_at' => now(),
                ],
            );
        });
    }
}

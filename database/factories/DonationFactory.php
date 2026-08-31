<?php

namespace Database\Factories;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Donation> */
class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'session_id' => fn (array $attributes) => Session::factory()->create([
                'tontine_id' => Membership::query()->findOrFail($attributes['membership_id'])->tontine_id,
            ]),
            'amount' => '10000.00',
            'reason' => fake()->sentence(),
            'status' => DonationStatus::Pending,
            'paid_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => ['status' => DonationStatus::Paid, 'paid_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => DonationStatus::Cancelled, 'paid_at' => null]);
    }
}

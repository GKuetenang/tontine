<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\Membership;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Loan> */
class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'session_id' => fn (array $attributes) => Session::factory()->create(['tontine_id' => Membership::query()->findOrFail($attributes['membership_id'])->tontine_id]),
            'principal_amount' => '10000.00',
            'interest_rate' => '10.00',
            'term_months' => 3,
            'interest_amount' => '1000.00',
            'total_due' => '11000.00',
            'due_at' => now()->addMonth(),
            'reason' => fake()->sentence(),
            'status' => LoanStatus::Pending,
            'created_by' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}

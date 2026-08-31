<?php

namespace Database\Factories;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Session;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => Session::factory(),
            'membership_id' => null,
            'transactionable_type' => null,
            'transactionable_id' => null,
            'type' => TransactionType::Donation,
            'direction' => TransactionDirection::Credit,
            'amount' => '1000.00',
            'description' => fake()->optional()->sentence(),
            'occurred_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}

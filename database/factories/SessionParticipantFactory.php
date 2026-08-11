<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\Session;
use App\Models\SessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionParticipant>
 */
class SessionParticipantFactory extends Factory
{
    protected $model = SessionParticipant::class;

    public function definition(): array
    {
        return [
            'session_id' => Session::factory(),
            'membership_id' => Membership::factory(),

            'contribution_amount' => 50_000,
            'draw_entries_count' => 1,

            'is_active' => true,

            'joined_at' => now(),
            'left_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(): array => [
            'is_active' => false,
            'left_at' => now(),
        ]);
    }
}

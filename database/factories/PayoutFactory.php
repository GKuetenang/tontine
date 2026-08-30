<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Models\Draw;
use App\Models\DrawEntry;
use App\Models\Meeting;
use App\Models\Payout;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),

            'draw_entry_id' => function (array $attributes): int {
                $meeting = Meeting::query()
                    ->findOrFail(
                        $attributes['meeting_id'],
                    );

                $draw = Draw::factory()
                    ->for($meeting->session)
                    ->create([
                        'confirmed_at' => now(),
                    ]);

                $participant = SessionParticipant::factory()
                    ->for($meeting->session)
                    ->create();

                return DrawEntry::factory()
                    ->for($draw)
                    ->for(
                        $participant,
                        'sessionParticipant',
                    )
                    ->create([
                        'position' => 1,
                        'entry_number' => 1,
                    ])
                    ->id;
            },

            'amount' => '500000.00',
            'status' => PayoutStatus::Pending,
            'paid_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => PayoutStatus::Pending,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => PayoutStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => PayoutStatus::Cancelled,
            'paid_at' => null,
        ]);
    }
}

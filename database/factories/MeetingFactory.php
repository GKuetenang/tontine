<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'session_id' => Session::factory(),
            'created_by' => User::factory(),

            'number' => 1,

            'title' => $title,

            'slug' => Str::slug($title)
                .'-'
                .Str::lower(Str::random(8)),

            'description' => fake()->optional()->sentence(),

            'scheduled_at' => fake()->dateTimeBetween(
                'now',
                '+6 months',
            ),

            'location' => fake()->optional()->city(),
            'duration_minutes' => fake()->numberBetween(1, 4) * 30,

            'status' => MeetingStatus::Scheduled,

            'opened_at' => null,
            'closed_at' => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => MeetingStatus::Scheduled,
            'opened_at' => null,
            'closed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => MeetingStatus::InProgress,
            'opened_at' => now(),
            'closed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => MeetingStatus::Completed,
            'opened_at' => now()->subHours(2),
            'closed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => MeetingStatus::Cancelled,
            'opened_at' => null,
            'closed_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\Tontine;
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
        $startAt = fake()->dateTimeBetween('-1 month', '+3 month');

        $year = fake()->unique()->numberBetween(2020, 2040);

        $name = "Session {$year}";

        return [
            'tontine_id' => Tontine::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'start_at' => $startAt,
            'end_at' => fake()->dateTimeBetween(
                $startAt,
                '+12 months',
            ),
            'is_active' => false,
            'is_closed' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn(): array => [
            'is_active' => true,
            'is_closed' => false,
            'activated_at' => now(),
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn(): array => [
            'is_active' => false,
            'is_closed' => true,
            'closed_at' => now(),
        ]);
    }
}

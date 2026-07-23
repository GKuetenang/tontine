<?php

namespace Database\Factories;

use App\Models\Tontine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tontine>
 */
class TontineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(10),
            'description' => $this->faker->text()
        ];
    }

    public function public(): static
    {
        return $this->state(fn() => [
            'is_public' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => [
            'is_active' => false,
        ]);
    }
}

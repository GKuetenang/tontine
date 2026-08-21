<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingDecision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingDecision>
 */
class MeetingDecisionFactory extends Factory
{
    protected $model = MeetingDecision::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'meeting_agenda_item_id' => null,
            'created_by' => User::factory(),

            'title' => fake()->sentence(4),
            'description' =>
            fake()->optional()->paragraph(),
        ];
    }
}

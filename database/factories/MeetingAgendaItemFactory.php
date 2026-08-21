<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingAgendaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingAgendaItem>
 */
class MeetingAgendaItemFactory extends Factory
{
    protected $model = MeetingAgendaItem::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'title' => fake()->sentence(4),
            'description' =>
            fake()->optional()->paragraph(),
            'position' => 1,
        ];
    }
}

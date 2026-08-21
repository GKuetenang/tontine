<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingNote>
 */
class MeetingNoteFactory extends Factory
{
    protected $model = MeetingNote::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'created_by' => User::factory(),
            'content' => fake()->paragraph(),
        ];
    }
}

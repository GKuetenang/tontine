<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\Meeting;
use App\Models\SessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),

            'session_participant_id' => SessionParticipant::factory(),

            'amount_due' => 40_000,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\SessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MeetingAttendance>
 */
class MeetingAttendanceFactory extends Factory
{
    protected $model = MeetingAttendance::class;

    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),

            'session_participant_id' => SessionParticipant::factory(),

            'status' => AttendanceStatus::Pending,

            'checked_in_at' => null,

            'note' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceStatus::Absent,
            'checked_in_at' => null,
        ]);
    }

    public function excused(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceStatus::Excused,
            'checked_in_at' => null,
        ]);
    }

    public function late(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceStatus::Late,
            'checked_in_at' => now(),
        ]);
    }
}

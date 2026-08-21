<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMeetingAttendance
 */
#[Fillable([
    'status',
    'checked_in_at',
    'note',
    'session_participant_id',
])]
class MeetingAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\MeetingAttendanceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'checked_in_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function sessionParticipant(): BelongsTo
    {
        return $this->belongsTo(
            SessionParticipant::class,
        );
    }
}

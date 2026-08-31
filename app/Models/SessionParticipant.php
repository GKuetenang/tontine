<?php

namespace App\Models;

use Database\Factories\SessionParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSessionParticipant
 */
#[Fillable([
    'contribution_amount',
    'draw_entries_count',
])]
class SessionParticipant extends Model
{
    /** @use HasFactory<SessionParticipantFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'contribution_amount' => 'integer',
            'draw_entries_count' => 'integer',
            'is_active' => 'boolean',
            'joined_at' => 'immutable_datetime',
            'left_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNull('left_at');
    }

    public function isActive(): bool
    {
        return $this->is_active
            && $this->left_at === null;
    }

    public function drawEntries(): HasMany
    {
        return $this->hasMany(DrawEntry::class);
    }

    public function meetingAttendances(): HasMany
    {
        return $this->hasMany(
            MeetingAttendance::class,
        );
    }
}

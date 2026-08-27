<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperMeeting
 */
#[Fillable([
    'number',
    'title',
    'description',
    'scheduled_at',
    'location',
])]
class Meeting extends Model
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'status' => MeetingStatus::class,

            'scheduled_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',

            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function isScheduled(): bool
    {
        return $this->status === MeetingStatus::Scheduled;
    }

    public function isInProgress(): bool
    {
        return $this->status === MeetingStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === MeetingStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === MeetingStatus::Cancelled;
    }

    public function agendaItems(): HasMany
    {
        return $this
            ->hasMany(MeetingAgendaItem::class)
            ->orderBy('position');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(
            MeetingAttendance::class,
        );
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MeetingNote::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(MeetingDecision::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(
            Payout::class,
        );
    }
}

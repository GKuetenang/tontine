<?php

namespace App\Models;

use App\Enums\DrawAllocationMode;
use App\Enums\SessionStatus;
use App\Models\Traits\HasSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @mixin IdeHelperSession
 */
#[Fillable(
    [
        'name',
        'description',
        'start_at',
        'end_at',
        'default_contribution_amount',
        'draw_allocation_mode',
        'base_contribution_amount',
        'status',
    ]
)]
class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use HasFactory;
    use HasSortable;
    use SoftDeletes;

    protected $table = 'tontine_sessions';

    protected $sortable = [
        'id',
        'name',
        'slug',
        'status',
        'start_at',
        'end_at',
        'created_at',
        'default_contribution_amount' => 'integer',
    ];

    protected $casts = [
        'status' => SessionStatus::class,
        'start_at' => 'immutable_datetime',
        'end_at' => 'immutable_datetime',
        'activated_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
        'draw_allocation_mode' => DrawAllocationMode::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function draw(): HasOne
    {
        return $this->hasOne(Draw::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this
            ->hasMany(SessionParticipant::class)
            ->active();
    }

    public function sessionParticipations(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }

    public function isDraft(): bool
    {
        return $this->status === SessionStatus::Draft;
    }

    public function isActive(): bool
    {
        return $this->status === SessionStatus::Active;
    }

    public function isClosed(): bool
    {
        return $this->status === SessionStatus::Closed;
    }
}

<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Policies\MembershipPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperMembership
 */
#[Fillable([
    'status',
    'member_number',
    'left_at',
    'status'
])]
#[UsePolicy(MembershipPolicy::class)]
class Membership extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'status' => MembershipStatus::class,
        'joined_at' => 'immutable_datetime',
        'left_at' => 'immutable_datetime',
        'verified_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MembershipStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::Active
            && $this->left_at === null
            && $this->deleted_at === null;
    }
}

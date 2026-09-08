<?php

namespace App\Models;

use App\Enums\PenaltyStatus;
use App\Enums\PenaltyTrigger;
use App\Models\Traits\HasSortable;
use App\Policies\PenaltyPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(PenaltyPolicy::class)]
#[Fillable([
    'penalty_rule_id',
    'meeting_id',
    'membership_id',
    'contribution_id',
    'trigger',
    'amount',
    'rule_name',
    'source',
    'status',
    'reason',
    'assessed_at',
    'created_by',
    'waived_at',
    'waived_by',
    'waiver_reason',
])]
class Penalty extends Model
{
    use HasFactory;
    use HasSortable;

    protected $sortable = ['assessed_at', 'amount', 'status', 'source', 'created_at'];

    protected function casts(): array
    {
        return [
            'trigger' => PenaltyTrigger::class,
            'status' => PenaltyStatus::class,
            'amount' => 'decimal:2',
            'assessed_at' => 'immutable_datetime',
            'waived_at' => 'immutable_datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PenaltyRule::class, 'penalty_rule_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}

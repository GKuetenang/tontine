<?php

namespace App\Models;

use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Traits\HasSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'type',
    'direction',
    'amount',
    'description',
    'occurred_at',
])]
class Transaction extends Model
{
    use HasFactory;
    use HasSortable;

    protected $sortable = [
        'id', 'type', 'direction', 'amount', 'occurred_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'direction' => TransactionDirection::class,
            'amount' => 'decimal:2',
            'occurred_at' => 'immutable_datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where(
            'direction',
            TransactionDirection::Credit,
        );
    }

    public function scopeDebits(Builder $query): Builder
    {
        return $query->where(
            'direction',
            TransactionDirection::Debit,
        );
    }
}

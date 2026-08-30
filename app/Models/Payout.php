<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'amount',
    'status',
    'paid_at',
])]
class Payout extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PayoutStatus::class,
            'paid_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(
            Meeting::class,
        );
    }

    public function drawEntry(): BelongsTo
    {
        return $this->belongsTo(
            DrawEntry::class,
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(
            Transaction::class,
            'transactionable',
        );
    }
}

<?php

namespace App\Models;

use App\Models\Traits\HasSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['amount', 'interest_amount', 'principal_amount', 'paid_at'])]
class Repayment extends Model
{
    use HasFactory;
    use HasSortable;

    protected $sortable = [
        'id', 'amount', 'interest_amount', 'principal_amount', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'interest_amount' => 'decimal:2', 'principal_amount' => 'decimal:2', 'paid_at' => 'immutable_datetime'];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}

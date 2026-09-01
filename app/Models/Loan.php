<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Models\Traits\HasSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['principal_amount', 'interest_rate', 'term_months', 'interest_amount', 'total_due', 'due_at', 'reason', 'status', 'approved_at'])]
class Loan extends Model
{
    use HasFactory;
    use HasSortable;

    protected $sortable = [
        'id', 'principal_amount', 'interest_rate', 'interest_amount',
        'total_due', 'due_at', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'term_months' => 'integer',
            'interest_amount' => 'decimal:2',
            'total_due' => 'decimal:2',
            'due_at' => 'immutable_date',
            'approved_at' => 'immutable_datetime',
            'status' => LoanStatus::class,
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
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }
}

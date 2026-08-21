<?php

namespace App\Models;

use App\Enums\ContributionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'amount_due',
    'session_participant_id',
])]
class Contribution extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount_due' => 'integer',
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

    public function transactions(): MorphMany
    {
        return $this->morphMany(
            Transaction::class,
            'transactionable',
        );
    }

    public function amountPaid(): int
    {
        return (int) $this
            ->transactions()
            ->credits()
            ->sum('amount');
    }

    public function remainingAmount(): int
    {
        return max(
            0,
            $this->amount_due - $this->amountPaid(),
        );
    }

    public function status(): ContributionStatus
    {
        $paid = $this->amountPaid();

        if ($paid <= 0) {
            return ContributionStatus::Unpaid;
        }

        if ($paid < $this->amount_due) {
            return ContributionStatus::Partial;
        }

        return ContributionStatus::Paid;
    }
}

<?php

namespace App\Models;

use Database\Factories\DrawEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperDrawEntry
 */
#[Fillable([
    'position',
    'entry_number',
    'session_participant_id',
])]
class DrawEntry extends Model
{
    /** @use HasFactory<DrawEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'entry_number' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function draw(): BelongsTo
    {
        return $this->belongsTo(Draw::class);
    }

    public function sessionParticipant(): BelongsTo
    {
        return $this->belongsTo(
            SessionParticipant::class
        );
    }

    public function payout(): HasOne
    {
        return $this->hasOne(
            Payout::class,
        );
    }
}

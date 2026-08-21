<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDrawEntry
 */
#[Fillable([
    'position',
    'entry_number',
    'session_participant_id'
])]
class DrawEntry extends Model
{
    /** @use HasFactory<\Database\Factories\DrawEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'entry_number' => 'integer',
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
}

<?php

namespace App\Models;

use Database\Factories\MeetingAgendaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMeetingAgendaItem
 */
#[Fillable([
    'title',
    'description',
    'position',
])]
class MeetingAgendaItem extends Model
{
    /** @use HasFactory<MeetingAgendaItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}

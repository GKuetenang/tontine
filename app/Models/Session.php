<?php

namespace App\Models;

use App\Models\Traits\HasSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @mixin IdeHelperSession
 */
#[Fillable(
    [
        'name',
        'description',
        'start_at',
        'end_at'
    ]
)]
class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use HasFactory;
    use HasSortable;
    use SoftDeletes;

    protected $table = 'tontine_sessions';

    protected $sortable = [
        'id',
        'name',
        'slug',
        'is_active',
        'is_closed',
        'start_at',
        'end_at',
        'created_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'start_at' => 'immutable_datetime',
        'end_at' => 'immutable_datetime',
        'activated_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }
}

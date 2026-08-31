<?php

namespace App\Models;

use Database\Factories\DrawFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperDraw
 */
#[Fillable(['description', 'created_by', 'confirmed_by'])]
class Draw extends Model
{
    /** @use HasFactory<DrawFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts()
    {
        return [
            'confirmed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
        );
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by',
        );
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function entries(): HasMany
    {
        return $this
            ->hasMany(DrawEntry::class)
            ->orderBy('position');
    }
}

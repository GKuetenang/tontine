<?php

namespace App\Models;

use App\Models\Traits\HasSortable;
use App\Policies\TontinePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'slug', 'description', 'currency'])]
#[UsePolicy(TontinePolicy::class)]
class Tontine extends Model implements HasMedia
{
    use HasSortable;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasFactory;

    protected $sortable = ['id', 'name', 'slug', 'description', 'is_active', 'is_public', 'currency'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 160, 160);
    }
}

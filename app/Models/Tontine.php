<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Models\Traits\HasSortable;
use App\Policies\TontinePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin IdeHelperTontine
 */
#[Fillable([
    'name',
    'slug',
    'description',
    'member_number_prefix',
    'currency',
    'default_membership_role'
])]
#[UsePolicy(TontinePolicy::class)]
class Tontine extends Model implements HasMedia
{
    use HasFactory;
    use HasSortable;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $sortable = [
        'id',
        'name',
        'member_number_prefix',
        'slug',
        'description',
        'is_active',
        'is_public',
        'currency'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    #[Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Membership>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot(
                'id',
                'member_number',
                'status',
                'joined_at',
                'left_at',
                'verified_at',
                'invited_by',
            )
            ->withTimestamps();
    }


    /**
     * Get the active tontines.
     *
     * @param  Builder<Tontine>  $query
     * @return Builder<Tontine>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default role name for a membership in this tontine.
     */
    public function defaultMembershipRoleName(): string
    {
        return $this->default_membership_role ?: config('memberships.default_role', 'member');
    }

    /**
     * Register the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    /**
     * Register the media conversions for the model.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 160, 160);
    }

    public function scopeAccessibleBy(
        Builder $query,
        User $user
    ): Builder {

        return $query->where(function (Builder $query) use ($user): void {
            $query
                ->where('user_id', $user->id)
                ->orWhereHas(
                    'memberships',
                    function (Builder $membershiptQuery) use ($user): void {
                        $membershiptQuery
                            ->where('user_id', $user->id)
                            ->where(
                                'status',
                                MembershipStatus::Active
                            );
                    }
                );
        });
    }

    public function hasActiveMembership(User $user)
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', MembershipStatus::Active)
            ->exists();
    }
}

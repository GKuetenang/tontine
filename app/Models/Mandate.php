<?php

namespace App\Models;

use App\Enums\MandateStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'starts_at', 'ends_at'])]
class Mandate extends Model
{
    use HasFactory;

    protected $casts = [
        'starts_at' => 'immutable_date',
        'ends_at' => 'immutable_date',
        'activated_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
        'status' => MandateStatus::class,
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(MandateRoleAssignment::class);
    }
}

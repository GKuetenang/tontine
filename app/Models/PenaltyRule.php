<?php

namespace App\Models;

use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use App\Models\Traits\HasSortable;
use App\Policies\PenaltyRulePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code',
    'name',
    'trigger',
    'calculation_type',
    'value',
    'grace_period',
    'grace_unit',
    'is_automatic',
    'is_active',
])]
#[UsePolicy(PenaltyRulePolicy::class)]
class PenaltyRule extends Model
{
    use HasSortable;

    protected $sortable = [
        'id',
        'name',
        'trigger',
        'calculation_type',
        'value',
        'grace_period',
        'is_automatic',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger' => PenaltyTrigger::class,
            'calculation_type' => PenaltyCalculationType::class,
            'grace_unit' => PenaltyGraceUnit::class,
            'value' => 'decimal:2',
            'grace_period' => 'integer',
            'is_automatic' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

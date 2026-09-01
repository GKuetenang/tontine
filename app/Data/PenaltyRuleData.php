<?php

namespace App\Data;

use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use App\Models\PenaltyRule;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'PenaltyRule')]
class PenaltyRuleData extends Data
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public PenaltyTrigger $trigger,
        public string $trigger_label,
        public PenaltyCalculationType $calculation_type,
        public string $calculation_type_label,
        public ?string $value,
        public string $value_label,
        public ?int $grace_period,
        public ?PenaltyGraceUnit $grace_unit,
        public ?string $grace_unit_label,
        public string $grace_period_label,
        public bool $is_automatic,
        public string $application_label,
        public bool $is_active,
        public string $status_label,
    ) {}

    public static function fromModel(PenaltyRule $rule): self
    {
        return new self(
            id: $rule->id,
            code: $rule->code,
            name: $rule->name,
            trigger: $rule->trigger,
            trigger_label: $rule->trigger->label(),
            calculation_type: $rule->calculation_type,
            calculation_type_label: $rule->calculation_type->label(),
            value: $rule->value,
            value_label: self::valueLabel($rule),
            grace_period: $rule->grace_period,
            grace_unit: $rule->grace_unit,
            grace_unit_label: $rule->grace_unit?->label(),
            grace_period_label: self::gracePeriodLabel($rule),
            is_automatic: $rule->is_automatic,
            application_label: $rule->is_automatic
                ? __('Automatique')
                : __('Manuelle'),
            is_active: $rule->is_active,
            status_label: $rule->is_active
                ? __('Active')
                : __('Inactive'),
        );
    }

    private static function valueLabel(PenaltyRule $rule): string
    {
        if ($rule->value === null) {
            return __('À configurer');
        }

        if ($rule->calculation_type === PenaltyCalculationType::Percentage) {
            return __(':value %', ['value' => $rule->value]);
        }

        return $rule->value;
    }

    private static function gracePeriodLabel(PenaltyRule $rule): string
    {
        if ($rule->grace_period === null || $rule->grace_unit === null) {
            return __('Aucune');
        }

        return __(':count :unit', [
            'count' => $rule->grace_period,
            'unit' => $rule->grace_unit->label(),
        ]);
    }
}

<?php

namespace App\Actions\Penalties;

use App\Enums\PenaltyTrigger;
use App\Models\Group;
use App\Models\PenaltyRule;
use App\Support\UniqueSlug;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SavePenaltyRuleAction
{
    public function __construct(private readonly UniqueSlug $uniqueSlug) {}

    public function execute(
        Group $group,
        array $attributes,
        ?PenaltyRule $rule = null,
    ): PenaltyRule {
        return DB::transaction(function () use ($group, $attributes, $rule): PenaltyRule {
            if ($rule !== null && $rule->group_id !== $group->id) {
                throw ValidationException::withMessages([
                    'rule' => __('Cette règle n’appartient pas à cette réunion.'),
                ]);
            }

            if (($attributes['is_active'] ?? false) && empty($attributes['value'])) {
                throw ValidationException::withMessages([
                    'value' => __('Un montant ou un pourcentage est obligatoire pour activer la règle.'),
                ]);
            }

            if (($attributes['trigger'] ?? null) === PenaltyTrigger::Manual->value) {
                $attributes['is_automatic'] = false;
            }

            if (empty($attributes['grace_period'])) {
                $attributes['grace_unit'] = null;
            }

            $rule ??= new PenaltyRule;
            if (! $rule->exists) {
                $attributes['code'] = $this->uniqueSlug->generate(
                    query: $group->penaltyRules()->getQuery(),
                    value: $attributes['name'],
                );
            }
            $rule->fill(Arr::only($attributes, $rule->getFillable()));
            $rule->group()->associate($group);
            $rule->save();

            return $rule->refresh();
        });
    }
}

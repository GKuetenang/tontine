<?php

namespace App\Actions\Penalties;

use App\Enums\PenaltyTrigger;
use App\Models\PenaltyRule;
use App\Models\Tontine;
use App\Support\UniqueSlug;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SavePenaltyRuleAction
{
    public function __construct(private readonly UniqueSlug $uniqueSlug) {}

    public function execute(
        Tontine $tontine,
        array $attributes,
        ?PenaltyRule $rule = null,
    ): PenaltyRule {
        return DB::transaction(function () use ($tontine, $attributes, $rule): PenaltyRule {
            if ($rule !== null && $rule->tontine_id !== $tontine->id) {
                throw ValidationException::withMessages([
                    'rule' => __('Cette règle n’appartient pas à cette tontine.'),
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
                    query: $tontine->penaltyRules()->getQuery(),
                    value: $attributes['name'],
                );
            }
            $rule->fill(Arr::only($attributes, $rule->getFillable()));
            $rule->tontine()->associate($tontine);
            $rule->save();

            return $rule->refresh();
        });
    }
}

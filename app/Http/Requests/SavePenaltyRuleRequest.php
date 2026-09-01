<?php

namespace App\Http\Requests;

use App\Enums\PenaltyCalculationType;
use App\Enums\PenaltyGraceUnit;
use App\Enums\PenaltyTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePenaltyRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'trigger' => ['required', Rule::enum(PenaltyTrigger::class)],
            'calculation_type' => ['required', Rule::enum(PenaltyCalculationType::class)],
            'value' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'gt:0',
                Rule::when(
                    $this->input('calculation_type') === PenaltyCalculationType::Percentage->value,
                    ['max:100'],
                    ['max:9999999999999.99'],
                ),
            ],
            'grace_period' => ['nullable', 'integer', 'min:0', 'max:525600'],
            'grace_unit' => ['nullable', 'required_with:grace_period', Rule::enum(PenaltyGraceUnit::class)],
            'is_automatic' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

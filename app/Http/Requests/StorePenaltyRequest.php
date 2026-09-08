<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenaltyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_id' => ['required', 'integer'],
            'meeting_id' => ['required', 'integer'],
            'penalty_rule_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}

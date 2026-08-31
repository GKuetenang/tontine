<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_id' => ['required', 'integer', 'exists:memberships,id'],
            'principal_amount' => ['required', 'decimal:0,2', 'gt:0'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

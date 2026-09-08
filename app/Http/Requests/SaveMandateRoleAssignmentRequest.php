<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMandateRoleAssignmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'membership_id' => ['required', 'integer', 'exists:memberships,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\MembershipStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('tontine_id', $this->route('tontine')->id)
                    ->where('guard_name', 'web')

            ],
            'status' => ['nullable', 'string', Rule::enum(MembershipStatus::class)]
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\MembershipStatus;
use App\Models\Group;
use App\Models\Membership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FormMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');
        $membership = $this->route('membership');

        if (! $group instanceof Group) {
            return false;
        }

        if ($membership instanceof Membership) {
            return Gate::allows('update', $membership);
        }

        return Gate::allows(
            'create',
            [Membership::class, $group],
        );
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('group_id', $this->route('group')->id)
                    ->where('guard_name', 'web'),
            ],
            'status' => ['nullable', 'string', Rule::enum(MembershipStatus::class)],
        ];
    }
}

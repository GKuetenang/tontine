<?php

namespace App\Http\Requests;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\Tontine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FormMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tontine = $this->route('tontine');
        $membership = $this->route('membership');

        if (! $tontine instanceof Tontine) {
            return false;
        }

        if ($membership instanceof Membership) {
            return Gate::allows('update', $membership);
        }

        return Gate::allows(
            'create',
            [Membership::class, $tontine],
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
                    ->where('tontine_id', $this->route('tontine')->id)
                    ->where('guard_name', 'web'),

            ],
            'status' => ['nullable', 'string', Rule::enum(MembershipStatus::class)],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\TontinePermission;
use App\Models\Tontine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SaveTontineRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('role')
            ? TontinePermission::UpdateRoles
            : TontinePermission::CreateRoles;

        return Gate::allows($permission->value);
    }

    public function rules(): array
    {
        /** @var Tontine $tontine */
        $tontine = $this->route('tontine');
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : null;
        $allowedPermissions = $this->user()
            ->getAllPermissions()
            ->pluck('name')
            ->intersect(TontinePermission::values())
            ->values()
            ->all();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('tontine_id', $tontine->id)
                    ->ignore($roleId),
            ],
            'permissions' => ['array'],
            'permissions.*' => [
                'string',
                Rule::in($allowedPermissions),
            ],
        ];
    }
}

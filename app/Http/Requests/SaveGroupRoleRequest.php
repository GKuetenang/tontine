<?php

namespace App\Http\Requests;

use App\Enums\GroupPermission;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SaveGroupRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('role')
            ? GroupPermission::UpdateRoles
            : GroupPermission::CreateRoles;

        return Gate::allows($permission->value);
    }

    public function rules(): array
    {
        /** @var Group $group */
        $group = $this->route('group');
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : null;
        $allowedPermissions = $this->user()
            ->getAllPermissions()
            ->pluck('name')
            ->intersect(GroupPermission::values())
            ->values()
            ->all();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('group_id', $group->id)
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

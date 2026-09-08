<?php

namespace App\Actions\Groups;

use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ForceDeleteGroupAction
{
    public function execute(Group $group): void
    {
        DB::transaction(function () use ($group): void {
            if ($group->sessions()->withTrashed()->exists()) {
                throw ValidationException::withMessages([
                    'group' => __('Supprimez définitivement les sessions de cette réunion avant de supprimer la réunion.'),
                ]);
            }

            $group->mandates()->delete();
            $group->memberships()->withTrashed()->forceDelete();

            foreach ($group->roles()->get() as $role) {
                $role->delete();
            }

            $group->forceDelete();
        });
    }
}

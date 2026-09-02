<?php

namespace App\Console\Commands;

use App\Actions\Groups\SyncGroupRolesAction;
use App\Models\Group;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('groups:sync-roles')]
#[Description('Synchronise les rôles et permissions de toutes les réunions')]
class SyncGroupRoles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        SyncGroupRolesAction $action,
    ): int {
        Group::query()
            ->each(
                fn (Group $group) => $action->execute($group),
            );

        $this->info(
            __('Les rôles et permissions ont été synchronisés.')
        );

        return self::SUCCESS;
    }
}

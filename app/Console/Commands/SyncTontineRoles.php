<?php

namespace App\Console\Commands;

use App\Actions\Tontines\SyncTontineRolesAction;
use App\Models\Tontine;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tontines:sync-roles')]
#[Description('Synchronise les rôles et permissions de toutes les tontines')]
class SyncTontineRoles extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(
        SyncTontineRolesAction $action,
    ): int {
        Tontine::query()
            ->each(
                fn (Tontine $tontine) => $action->execute($tontine),
            );

        $this->info(
            __('Les rôles et permissions ont été synchronisés.')
        );

        return self::SUCCESS;
    }
}

<?php

namespace App\Actions\Sessions;

use App\Models\Session;
use Illuminate\Support\Facades\DB;

final class RestoreSessionAction
{
    public function execute(Session $session): void
    {
        DB::transaction(fn () => $session->restore());
    }
}

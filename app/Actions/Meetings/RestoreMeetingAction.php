<?php

namespace App\Actions\Meetings;

use App\Models\Meeting;
use Illuminate\Support\Facades\DB;

final class RestoreMeetingAction
{
    public function execute(Meeting $meeting): void
    {
        DB::transaction(fn () => $meeting->restore());
    }
}

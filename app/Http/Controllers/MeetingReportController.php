<?php

namespace App\Http\Controllers;

use App\Actions\Reports\BuildMeetingReportAction;
use App\Data\SessionData;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Tontine;
use Inertia\Inertia;
use Inertia\Response;

class MeetingReportController extends Controller
{
    public function show(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        BuildMeetingReportAction $buildReport,
    ): Response {
        $this->authorize(
            'report',
            $meeting,
        );

        return Inertia::render('meeting-reports/show', [
            'tontine' => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],
            'session' => SessionData::fromModel($session),
            'report' => $buildReport->execute($meeting),
            'canExport' => request()->user()->can(
                'exportReport',
                $meeting,
            ),
        ]);
    }
}

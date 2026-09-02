<?php

namespace App\Http\Controllers;

use App\Actions\Reports\BuildMeetingReportAction;
use App\Data\SessionData;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\Session;
use Inertia\Inertia;
use Inertia\Response;

class MeetingReportController extends Controller
{
    public function show(
        Group $group,
        Session $session,
        Meeting $meeting,
        BuildMeetingReportAction $buildReport,
    ): Response {
        $this->authorize(
            'report',
            $meeting,
        );

        return Inertia::render('meeting-reports/show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
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

<?php

namespace App\Http\Controllers;

use App\Actions\Meetings\CancelMeetingAction;
use App\Actions\Meetings\CloseMeetingAction;
use App\Actions\Meetings\CreateMeetingAction;
use App\Actions\Meetings\OpenMeetingAction;
use App\Actions\Meetings\UpdateMeetingAction;
use App\Actions\Payouts\BuildMeetingPayoutContextAction;
use App\Data\MeetingData;
use App\Data\MeetingScheduleData;
use App\Data\SessionData;
use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Models\Meeting;
use App\Models\Session;
use App\Models\Tontine;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MeetingController extends Controller
{
    public function index(
        Tontine $tontine,
        Session $session,
    ): Response {
        Gate::authorize(
            'viewAny',
            [Meeting::class, $session],
        );

        $meetings = $session
            ->meetings()
            ->withCount([
                'attendances',
                'contributions',
            ])
            ->orderByDesc('scheduled_at')
            ->paginate(10)
            ->withQueryString();
        $session->load('meetingSchedule');

        return Inertia::render('meetings/index', [
            'tontine' => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],

            'session' => SessionData::fromModel(
                $session,
            ),

            'collection' => MeetingData::collect(
                $meetings,
            ),
            'meeting' => new Meeting,
            'meeting_schedule' => $session->meetingSchedule
                ? MeetingScheduleData::fromModel($session->meetingSchedule)
                : null,
            'meeting_recurrences' => MeetingRecurrence::getOptions(),
            'meeting_monthly_patterns' => MeetingMonthlyPattern::getOptions(),
            'timezones' => collect(DateTimeZone::listIdentifiers())
                ->map(fn(string $timezone): array => [
                    'label' => $timezone,
                    'value' => $timezone,
                ]),
        ]);
    }

    public function show(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        BuildMeetingPayoutContextAction $buildPayoutContext,
    ): Response {
        Gate::authorize('view', $meeting);

        $meeting->load([
            'agendaItems',
            'attendances.sessionParticipant.membership.user',

            'contributions.sessionParticipant.membership.user',
            'contributions.transactions',

            'notes.agendaItem',
            'notes.creator',

            'decisions.agendaItem',
            'decisions.creator',

            'payouts' => fn($query) => $query->latest(),

            'payouts.drawEntry.sessionParticipant.membership.user',
            'payouts.creator',
            'payouts.transactions',

            'session.draw.entries.sessionParticipant.membership.user',
        ]);

        $payoutContext =
            $buildPayoutContext->execute(
                $meeting,
            );

        return Inertia::render('meetings/show', [
            'tontine' => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],

            'session' => fn() => SessionData::fromModel(
                $session,
            ),

            'meeting' => fn() => MeetingData::fromModel(
                $meeting,
            ),
            'payoutContext' => $payoutContext,
        ]);
    }

    public function store(
        StoreMeetingRequest $request,
        Tontine $tontine,
        Session $session,
        CreateMeetingAction $action,
    ): RedirectResponse {
        $this->authorize(
            'create',
            [Meeting::class, $session],
        );

        $action->execute(
            session: $session,
            creator: $request->user(),
            title: $request
                ->string('title')
                ->toString(),
            scheduledAt: CarbonImmutable::parse(
                $request
                    ->string('scheduled_at')
                    ->toString(),
            ),
            description: $request->input(
                'description',
            ),
            location: $request->input(
                'location',
            ),
        );

        return Inertia::flash(
            'success',
            __(
                'La réunion a été créée avec succès.'
            ),
        )->back();
    }

    public function update(
        UpdateMeetingRequest $request,
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        UpdateMeetingAction $action,
    ): RedirectResponse {
        $this->authorize(
            'update',
            [Meeting::class, $meeting],
        );

        $action->execute(
            meeting: $meeting,
            title: $request
                ->string('title')
                ->toString(),
            scheduledAt: CarbonImmutable::parse(
                $request
                    ->string('scheduled_at')
                    ->toString(),
            ),
            description: $request->input(
                'description',
            ),
            location: $request->input(
                'location',
            ),
        );

        return Inertia::flash(
            'success',
            __(
                'La réunion a été modifiée avec succès.'
            ),
        )->back();
    }

    public function open(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        OpenMeetingAction $action,
    ): RedirectResponse {
        $this->authorize(
            'open',
            $meeting,
        );

        $action->execute($meeting);

        return Inertia::flash(
            'success',
            __(
                'La réunion a été ouverte avec succès.'
            ),
        )->back();
    }

    public function close(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        CloseMeetingAction $action,
    ): RedirectResponse {
        $this->authorize(
            'close',
            $meeting,
        );

        $action->execute($meeting);

        return Inertia::flash(
            'success',
            __(
                'La réunion a été clôturée avec succès.'
            ),
        )->back();
    }

    public function cancel(
        Tontine $tontine,
        Session $session,
        Meeting $meeting,
        CancelMeetingAction $action,
    ): RedirectResponse {
        $this->authorize(
            'cancel',
            $meeting,
        );

        $action->execute($meeting);

        return Inertia::flash(
            'success',
            __(
                'La réunion a été annulée avec succès.'
            ),
        )->back();
    }
}

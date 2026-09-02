<?php

namespace App\Http\Controllers;

use App\Actions\Meetings\GenerateRecurringMeetingsAction;
use App\Actions\Meetings\UpdateRecurringMeetingsAction;
use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Http\Requests\StoreMeetingScheduleRequest;
use App\Models\Group;
use App\Models\Session;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class MeetingScheduleController extends Controller
{
    public function store(
        StoreMeetingScheduleRequest $request,
        Group $group,
        Session $session,
        GenerateRecurringMeetingsAction $generateMeetings,
    ): RedirectResponse {
        $validated = $request->validated();

        $generateMeetings->execute(
            session: $session,
            creator: $request->user(),
            recurrence: MeetingRecurrence::from($validated['recurrence']),
            startsAt: CarbonImmutable::parse($validated['starts_at'], $validated['timezone']),
            timezone: $validated['timezone'],
            defaultTitle: $validated['default_title'],
            defaultLocation: $validated['default_location'] ?? null,
            defaultDurationMinutes: $validated['default_duration_minutes'],
            monthlyPattern: MeetingMonthlyPattern::from(
                $validated['monthly_pattern'] ?? MeetingMonthlyPattern::DayOfMonth->value,
            ),
            interval: $validated['interval'],
        );

        return Inertia::flash('success', __('Le calendrier des assises a été généré avec succès.'))->back();
    }

    public function update(
        StoreMeetingScheduleRequest $request,
        Group $group,
        Session $session,
        UpdateRecurringMeetingsAction $updateMeetings,
    ): RedirectResponse {
        $validated = $request->validated();

        $updateMeetings->execute(
            session: $session,
            recurrence: MeetingRecurrence::from($validated['recurrence']),
            startsAt: CarbonImmutable::parse($validated['starts_at'], $validated['timezone']),
            timezone: $validated['timezone'],
            defaultTitle: $validated['default_title'],
            defaultLocation: $validated['default_location'] ?? null,
            defaultDurationMinutes: $validated['default_duration_minutes'],
            monthlyPattern: MeetingMonthlyPattern::from(
                $validated['monthly_pattern'] ?? MeetingMonthlyPattern::DayOfMonth->value,
            ),
            interval: $validated['interval'],
        );

        return Inertia::flash('success', __('Le calendrier des assises a été mis à jour avec succès.'))->back();
    }
}

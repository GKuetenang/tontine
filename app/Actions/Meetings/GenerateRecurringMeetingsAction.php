<?php

namespace App\Actions\Meetings;

use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingSchedule;
use App\Models\Session;
use App\Models\User;
use App\Support\UniqueSlug;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class GenerateRecurringMeetingsAction
{
    public function __construct(
        private readonly UniqueSlug $uniqueSlug,
        private readonly BuildMeetingOccurrencesAction $buildOccurrences,
    ) {}

    public function execute(
        Session $session,
        User $creator,
        MeetingRecurrence $recurrence,
        CarbonImmutable|string $startsAt,
        string $timezone,
        string $defaultTitle,
        ?string $defaultLocation,
        int $defaultDurationMinutes,
        MeetingMonthlyPattern $monthlyPattern = MeetingMonthlyPattern::DayOfMonth,
        int $interval = 1,
    ): MeetingSchedule {
        return DB::transaction(function () use ($session, $creator, $recurrence, $startsAt, $timezone, $defaultTitle, $defaultLocation, $defaultDurationMinutes, $monthlyPattern, $interval): MeetingSchedule {
            $lockedSession = Session::query()->lockForUpdate()->findOrFail($session->id);

            if (! $lockedSession->isDraft()) {
                throw ValidationException::withMessages([
                    'schedule' => __('Le calendrier des assises ne peut être généré que pour une session en brouillon.'),
                ]);
            }

            if ($lockedSession->start_at === null || $lockedSession->end_at === null) {
                throw ValidationException::withMessages([
                    'schedule' => __('Les dates de début et de fin de la session sont obligatoires.'),
                ]);
            }

            $firstOccurrence = $startsAt instanceof CarbonImmutable
                ? $startsAt
                : CarbonImmutable::parse($startsAt, $timezone);

            if ($firstOccurrence->lt($lockedSession->start_at) || $firstOccurrence->gt($lockedSession->end_at)) {
                throw ValidationException::withMessages([
                    'starts_at' => __('La première assise doit être comprise dans les dates de la session.'),
                ]);
            }

            $schedule = $lockedSession->meetingSchedule()->first();

            $rrule = $recurrence->rrule($monthlyPattern, $firstOccurrence, $interval);

            if ($schedule !== null && $schedule->rrule !== $rrule) {
                throw ValidationException::withMessages([
                    'schedule' => __('Un calendrier différent a déjà été généré pour cette session.'),
                ]);
            }

            $schedule ??= new MeetingSchedule;
            $schedule->fill([
                'rrule' => $rrule,
                'starts_at' => $firstOccurrence,
                'timezone' => $timezone,
                'default_title' => $defaultTitle,
                'default_location' => $defaultLocation,
                'default_duration_minutes' => $defaultDurationMinutes,
            ]);
            $schedule->session()->associate($lockedSession);
            $schedule->creator()->associate($creator);
            $schedule->save();

            $occurrences = $this->buildOccurrences->execute(
                rrule: $rrule,
                startsAt: $firstOccurrence,
                endsAt: CarbonImmutable::instance($lockedSession->end_at),
            );
            $nextNumber = ((int) $lockedSession->meetings()->max('number')) + 1;

            foreach ($occurrences as $occurrence) {
                if ($schedule->meetings()->where('scheduled_at', $occurrence)->exists()) {
                    continue;
                }

                $meeting = new Meeting;
                $meeting->fill([
                    'number' => $nextNumber,
                    'title' => $defaultTitle.' '.$nextNumber,
                    'scheduled_at' => $occurrence,
                    'location' => $defaultLocation,
                    'duration_minutes' => $defaultDurationMinutes,
                ]);
                $meeting->session()->associate($lockedSession);
                $meeting->meetingSchedule()->associate($schedule);
                $meeting->creator()->associate($creator);
                $meeting->status = MeetingStatus::Scheduled;
                $meeting->slug = $this->uniqueSlug->generate(
                    query: $lockedSession->meetings()->getQuery(),
                    value: $meeting->title,
                );
                $meeting->save();
                $nextNumber++;
            }

            $schedule->update(['generated_at' => now()]);

            return $schedule->refresh();
        });
    }
}

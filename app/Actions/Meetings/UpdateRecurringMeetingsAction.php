<?php

namespace App\Actions\Meetings;

use App\Enums\MeetingMonthlyPattern;
use App\Enums\MeetingRecurrence;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingSchedule;
use App\Models\Session;
use App\Support\UniqueSlug;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateRecurringMeetingsAction
{
    public function __construct(
        private readonly UniqueSlug $uniqueSlug,
        private readonly BuildMeetingOccurrencesAction $buildOccurrences,
    ) {}

    public function execute(
        Session $session,
        MeetingRecurrence $recurrence,
        CarbonImmutable|string $startsAt,
        string $timezone,
        string $defaultTitle,
        ?string $defaultLocation,
        int $defaultDurationMinutes,
        MeetingMonthlyPattern $monthlyPattern = MeetingMonthlyPattern::DayOfMonth,
        int $interval = 1,
    ): MeetingSchedule {
        return DB::transaction(function () use ($session, $recurrence, $startsAt, $timezone, $defaultTitle, $defaultLocation, $defaultDurationMinutes, $monthlyPattern, $interval): MeetingSchedule {
            $lockedSession = Session::query()->lockForUpdate()->findOrFail($session->id);

            if (! $lockedSession->isDraft()) {
                throw ValidationException::withMessages([
                    'schedule' => __('Le calendrier des assises ne peut être modifié que pour une session en brouillon.'),
                ]);
            }

            if ($lockedSession->start_at === null || $lockedSession->end_at === null) {
                throw ValidationException::withMessages([
                    'schedule' => __('Les dates de début et de fin de la session sont obligatoires.'),
                ]);
            }

            $schedule = $lockedSession->meetingSchedule()->lockForUpdate()->first();

            if ($schedule === null) {
                throw ValidationException::withMessages([
                    'schedule' => __('Aucun calendrier d’assises n’a été configuré pour cette session.'),
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

            $rrule = $recurrence->rrule($monthlyPattern, $firstOccurrence, $interval);
            $occurrences = $this->buildOccurrences->execute(
                rrule: $rrule,
                startsAt: $firstOccurrence,
                endsAt: CarbonImmutable::instance($lockedSession->end_at),
            );
            $meetings = $schedule->meetings()
                ->lockForUpdate()
                ->orderBy('number')
                ->get();

            $this->assertMeetingsCanBeSynchronized($meetings, count($occurrences));

            // Avoid transient collisions on the unique schedule/date constraint.
            $schedule->meetings()->update(['meeting_schedule_id' => null]);

            foreach ($occurrences as $index => $occurrence) {
                /** @var Meeting|null $meeting */
                $meeting = $meetings->get($index);

                if ($meeting === null) {
                    $meeting = new Meeting;
                    $meeting->session()->associate($lockedSession);
                    $meeting->creator()->associate($schedule->creator);
                    $meeting->number = ((int) $lockedSession->meetings()->max('number')) + 1;
                    $meeting->status = MeetingStatus::Scheduled;
                    $meeting->slug = $this->uniqueSlug->generate(
                        query: $lockedSession->meetings()->getQuery(),
                        value: $defaultTitle.' '.$meeting->number,
                    );
                }

                $meeting->fill([
                    'title' => $defaultTitle.' '.$meeting->number,
                    'scheduled_at' => $occurrence,
                    'location' => $defaultLocation,
                    'duration_minutes' => $defaultDurationMinutes,
                ]);
                $meeting->meetingSchedule()->associate($schedule);
                $meeting->save();
            }

            $meetings->slice(count($occurrences))->each(function (Meeting $meeting): void {
                $meeting->forceDelete();
            });

            $schedule->update([
                'rrule' => $rrule,
                'starts_at' => $firstOccurrence,
                'timezone' => $timezone,
                'default_title' => $defaultTitle,
                'default_location' => $defaultLocation,
                'default_duration_minutes' => $defaultDurationMinutes,
                'generated_at' => now(),
            ]);

            return $schedule->refresh();
        });
    }

    /** @param Collection<int, Meeting> $meetings */
    private function assertMeetingsCanBeSynchronized(Collection $meetings, int $occurrenceCount): void
    {
        if ($meetings->contains(fn (Meeting $meeting): bool => ! $meeting->isScheduled())) {
            throw ValidationException::withMessages([
                'schedule' => __('Le calendrier ne peut plus être modifié globalement car une assise a déjà changé de statut.'),
            ]);
        }

        $surplusMeetings = $meetings->slice($occurrenceCount);

        foreach ($surplusMeetings as $meeting) {
            if ($this->hasBusinessData($meeting)) {
                throw ValidationException::withMessages([
                    'schedule' => __('La nouvelle récurrence retirerait une assise qui contient déjà des données.'),
                ]);
            }
        }
    }

    private function hasBusinessData(Meeting $meeting): bool
    {
        return $meeting->agendaItems()->exists()
            || $meeting->attendances()->exists()
            || $meeting->contributions()->exists()
            || $meeting->notes()->exists()
            || $meeting->decisions()->exists()
            || $meeting->payouts()->exists();
    }
}

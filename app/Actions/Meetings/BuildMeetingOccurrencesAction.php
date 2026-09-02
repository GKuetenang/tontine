<?php

namespace App\Actions\Meetings;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use RRule\RRule;

final class BuildMeetingOccurrencesAction
{
    private const MAX_OCCURRENCES = 250;

    /** @return list<CarbonImmutable> */
    public function execute(
        string $rrule,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): array {
        $rule = new RRule(
            $rrule,
            $startsAt->toDateTimeImmutable(),
        );
        $occurrences = $rule->getOccurrencesBetween(
            $startsAt->toDateTimeImmutable(),
            $endsAt->toDateTimeImmutable(),
            self::MAX_OCCURRENCES + 1,
        );

        if (count($occurrences) > self::MAX_OCCURRENCES) {
            throw ValidationException::withMessages([
                'schedule' => __('Le calendrier contient trop d’assises.'),
            ]);
        }

        return array_map(
            fn (\DateTimeInterface $occurrence): CarbonImmutable => CarbonImmutable::instance($occurrence),
            $occurrences,
        );
    }
}

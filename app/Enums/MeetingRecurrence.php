<?php

namespace App\Enums;

use Carbon\CarbonImmutable;

enum MeetingRecurrence: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public static function getOptions(): array
    {
        return array_map(
            fn (self $recurrence): array => [
                'label' => $recurrence->label(),
                'value' => $recurrence->value,
            ],
            [
                self::Weekly,
                self::Monthly,
            ],
        );
    }

    public static function fromRRule(string $rrule): self
    {
        if (str_contains($rrule, 'FREQ=MONTHLY')) {
            return self::Monthly;
        }

        return self::Weekly;
    }

    public function label(): string
    {
        return match ($this) {
            self::Weekly => __('Semaine(s)'),
            self::Monthly => __('Mois'),
        };
    }

    public function rrule(
        MeetingMonthlyPattern $monthlyPattern = MeetingMonthlyPattern::DayOfMonth,
        ?CarbonImmutable $startsAt = null,
        int $interval = 1,
    ): string {
        if (
            $this === self::Monthly
            && $monthlyPattern === MeetingMonthlyPattern::WeekdayOrdinal
            && $startsAt !== null
        ) {
            $ordinal = intdiv($startsAt->day - 1, 7) + 1;
            $weekday = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][$startsAt->dayOfWeek];

            return "FREQ=MONTHLY;INTERVAL={$interval};BYDAY={$ordinal}{$weekday}";
        }

        if (
            $this === self::Monthly
            && $monthlyPattern === MeetingMonthlyPattern::DayOfMonth
            && $startsAt?->isLastOfMonth()
        ) {
            return "FREQ=MONTHLY;INTERVAL={$interval};BYMONTHDAY=-1";
        }

        return match ($this) {
            self::Weekly => "FREQ=WEEKLY;INTERVAL={$interval}",
            self::Monthly => "FREQ=MONTHLY;INTERVAL={$interval}",
        };
    }
}

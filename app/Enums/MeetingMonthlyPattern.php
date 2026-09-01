<?php

namespace App\Enums;

enum MeetingMonthlyPattern: string
{
    case DayOfMonth = 'day_of_month';
    case WeekdayOrdinal = 'weekday_ordinal';

    public static function getOptions(): array
    {
        return array_map(
            fn (self $pattern): array => [
                'label' => $pattern->label(),
                'value' => $pattern->value,
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::DayOfMonth => __('Le même jour du mois'),
            self::WeekdayOrdinal => __('Le même rang de jour de semaine'),
        };
    }
}

<?php

namespace App\Enums;

enum PenaltyGraceUnit: string
{
    case Minutes = 'minutes';
    case Days = 'days';

    public static function getOptions(): array
    {
        return array_map(fn (self $unit): array => [
            'label' => $unit->label(),
            'value' => $unit->value,
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Minutes => __('Minute(s)'),
            self::Days => __('Jour(s)'),
        };
    }
}

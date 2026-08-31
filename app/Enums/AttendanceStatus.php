<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Pending = 'pending';
    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';
    case Late = 'late';

    public static function getOptions(): array
    {
        return array_map(
            fn (self $status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('En attente'),
            self::Present => __('Présent'),
            self::Absent => __('Absent'),
            self::Excused => __('Absent justifié'),
            self::Late => __('En retard'),
        };
    }
}

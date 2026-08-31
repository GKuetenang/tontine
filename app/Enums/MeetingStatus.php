<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

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
            self::Scheduled => __('Prévue'),
            self::InProgress => __('En cours'),
            self::Completed => __('Terminée'),
            self::Cancelled => __('Annulée'),
        };
    }
}

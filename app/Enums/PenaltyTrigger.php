<?php

namespace App\Enums;

enum PenaltyTrigger: string
{
    case MeetingLate = 'meeting_late';
    case MeetingAbsent = 'meeting_absent';
    case ContributionLate = 'contribution_late';
    case ContributionIncomplete = 'contribution_incomplete';
    case Manual = 'manual';

    public static function getOptions(): array
    {
        return array_map(fn (self $trigger): array => [
            'label' => $trigger->label(),
            'value' => $trigger->value,
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::MeetingLate => __('Retard à une assise'),
            self::MeetingAbsent => __('Absence à une assise'),
            self::ContributionLate => __('Retard de cotisation'),
            self::ContributionIncomplete => __('Cotisation incomplète'),
            self::Manual => __('Pénalité manuelle'),
        };
    }
}

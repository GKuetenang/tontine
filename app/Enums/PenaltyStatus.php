<?php

namespace App\Enums;

enum PenaltyStatus: string
{
    case Pending = 'pending';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('À payer'),
            self::Waived => __('Exemptée'),
        };
    }
}

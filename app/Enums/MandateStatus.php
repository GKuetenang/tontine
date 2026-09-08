<?php

namespace App\Enums;

enum MandateStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Brouillon'),
            self::Active => __('Actif'),
            self::Closed => __('Clôturé'),
        };
    }
}

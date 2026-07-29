<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Left = 'left';

    public static function getOptions(): array
    {
        return array_map(fn(self $status) => [
            'label' => $status->label(),
            'value' => $status->value,
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Actif'),
            self::Inactive => __('Inactif'),
            self::Suspended => __('Suspendu'),
            self::Left => __('A quitté'),
        };
    }
}

<?php

namespace App\Enums;

enum TontineRole: string
{
    case President = 'president';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Member = 'member';
    case Censor = 'censor';
    case Guest = 'guest';


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
            self::President => __('Président'),
            self::Secretary => __('Secrétaire'),
            self::Treasurer => __('Trésorier'),
            self::Member => __('Membre'),
            self::Censor => __('Censeur'),
            self::Guest => __('Invité'),
        };
    }
}

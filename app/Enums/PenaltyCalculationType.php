<?php

namespace App\Enums;

enum PenaltyCalculationType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public static function getOptions(): array
    {
        return array_map(fn (self $type): array => [
            'label' => $type->label(),
            'value' => $type->value,
        ], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('Montant fixe'),
            self::Percentage => __('Pourcentage de la cotisation'),
        };
    }
}

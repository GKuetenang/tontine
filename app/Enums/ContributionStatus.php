<?php

namespace App\Enums;

enum ContributionStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public static function getOptions(): array
    {
        return array_map(
            fn(self $status) => [
                'label' => $status->label(),
                'value' => $status->value,
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => __('Non payée'),
            self::Partial => __('Partiellement payée'),
            self::Paid => __('Payée'),
        };
    }
}

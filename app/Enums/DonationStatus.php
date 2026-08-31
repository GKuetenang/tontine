<?php

namespace App\Enums;

enum DonationStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public static function getOptions(): array
    {
        return array_map(fn (self $status): array => ['label' => $status->label(), 'value' => $status->value], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('En attente'), self::Paid => __('Effectué'), self::Cancelled => __('Annulé')
        };
    }
}

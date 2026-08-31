<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Repaid = 'repaid';
    case Cancelled = 'cancelled';

    public static function getOptions(): array
    {
        return array_map(fn (self $status): array => ['label' => $status->label(), 'value' => $status->value], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('En attente'), self::Active => __('Actif'), self::Repaid => __('Remboursé'), self::Cancelled => __('Annulé')
        };
    }
}

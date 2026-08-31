<?php

namespace App\Enums;

enum TransactionDirection: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    public static function getOptions(): array
    {
        return array_map(fn (self $direction): array => ['label' => $direction->label(), 'value' => $direction->value], self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Credit => __('Crédit'), self::Debit => __('Débit')
        };
    }
}

<?php

namespace App\Enums;

enum TransactionType: string
{
    case Contribution = 'contribution';
    case Loan = 'loan';
    case Repayment = 'repayment';
    case Penalty = 'penalty';
    case Insurance = 'insurance';
    case Donation = 'donation';
    case Payout = 'payout';

    public static function getOptions(): array
    {
        return array_map(
            fn (self $type): array => [
                'label' => $type->label(),
                'value' => $type->value,
            ],
            self::cases()
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Contribution => __('Cotisation'),
            self::Loan => __('Prêt'),
            self::Repayment => __('Remboursement'),
            self::Penalty => __('Pénalité'),
            self::Insurance => __('Assurance'),
            self::Donation => __('Don'),
            self::Payout => __('Versement'),
        };
    }
}

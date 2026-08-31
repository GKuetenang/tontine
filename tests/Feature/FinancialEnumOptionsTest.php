<?php

use App\Enums\DonationStatus;
use App\Enums\LoanStatus;
use App\Enums\PayoutStatus;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;

it('exposes financial enum labels and options from the backend', function (): void {
    expect(TransactionType::getOptions())->toContain(['label' => 'Prêt', 'value' => 'loan'])
        ->and(TransactionDirection::getOptions())->toContain(['label' => 'Crédit', 'value' => 'credit'])
        ->and(LoanStatus::getOptions())->toContain(['label' => 'Remboursé', 'value' => 'repaid'])
        ->and(DonationStatus::getOptions())->toContain(['label' => 'Effectué', 'value' => 'paid'])
        ->and(PayoutStatus::getOptions())->toContain(['label' => 'Payé', 'value' => 'paid']);
});

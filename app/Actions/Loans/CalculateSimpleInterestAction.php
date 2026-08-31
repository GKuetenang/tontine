<?php

namespace App\Actions\Loans;

use Illuminate\Validation\ValidationException;

final class CalculateSimpleInterestAction
{
    /** @return array{interest: string, total: string} */
    public function execute(string $principal, string $rate): array
    {
        $principalCents = $this->decimalToInteger($principal, 2, 'principal_amount');
        $rateBasisPoints = $this->decimalToInteger($rate, 2, 'interest_rate');

        if ($principalCents <= 0 || $rateBasisPoints < 0) {
            throw ValidationException::withMessages(['principal_amount' => __('Le capital doit être positif et le taux ne peut pas être négatif.')]);
        }

        $interestCents = intdiv(($principalCents * $rateBasisPoints) + 5000, 10000);

        return [
            'interest' => $this->formatCents($interestCents),
            'total' => $this->formatCents($principalCents + $interestCents),
        ];
    }

    private function decimalToInteger(string $value, int $scale, string $field): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,'.$scale.'})?$/', $value)) {
            throw ValidationException::withMessages([$field => __('La valeur décimale est invalide.')]);
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * (10 ** $scale)) + (int) str_pad($fraction, $scale, '0');
    }

    private function formatCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}

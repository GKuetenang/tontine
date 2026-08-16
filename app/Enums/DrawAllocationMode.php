<?php

namespace App\Enums;

enum DrawAllocationMode: string
{
    case OnePerMember = 'one_per_member';
    case BasedOnContribution = 'based_on_contribution';
    case Custom = 'custom';

    public static function getOptions(): array
    {
        return array_map(
            fn(self $mode) => [
                'label' => $mode->label(),
                'value' => $mode->value,
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::OnePerMember => __('Un tour par membre'),
            self::BasedOnContribution => __('Selon la cotisation'),
            self::Custom => __('Personnalisé'),
        };
    }
}

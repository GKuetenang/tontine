<?php

namespace App\Enums;

enum DrawAllocationMode: string
{
    case OnePerMember = 'one_per_member';
    case BasedOnContribution = 'based_on_contribution';
    case Custom = 'custom';
}

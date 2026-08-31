<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Repaid = 'repaid';
    case Cancelled = 'cancelled';
}

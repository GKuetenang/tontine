<?php

namespace App\Enums;

enum TransactionType: string
{
    case Contribution = 'contribution';
    case Loan = 'loan';
    case Repayment = 'repayment';
    case Penalty = 'penalty';
    case CashFund = 'cash_fund';
    case Donation = 'donation';
    case Payout = 'payout';
}

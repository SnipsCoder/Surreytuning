<?php

namespace App\Enums;

enum FileCreditTransactionType: string
{
    case TopUp = 'top_up';
    case Deduction = 'deduction';
    case ManualCredit = 'manual_credit';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::TopUp => 'Top-Up',
            self::Deduction => 'Deduction',
            self::ManualCredit => 'Manual Credit',
            self::Refund => 'Refund',
        };
    }
}

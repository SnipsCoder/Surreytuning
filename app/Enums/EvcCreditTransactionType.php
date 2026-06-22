<?php

namespace App\Enums;

enum EvcCreditTransactionType: string
{
    case Purchase = 'purchase';
    case ManualCredit = 'manual_credit';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::ManualCredit => 'Manual Credit',
            self::Refund => 'Refund',
        };
    }
}

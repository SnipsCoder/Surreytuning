<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Issued = 'issued';
    case Paid = 'paid';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Issued => 'Issued',
            self::Paid => 'Paid',
            self::Void => 'Void',
        };
    }
}

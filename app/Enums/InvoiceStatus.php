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

    public function colour(): string
    {
        return match ($this) {
            self::Issued => 'bg-yellow-100 text-yellow-800',
            self::Paid => 'bg-green-100 text-green-800',
            self::Void => 'bg-gray-100 text-gray-600',
        };
    }
}

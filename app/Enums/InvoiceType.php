<?php

namespace App\Enums;

enum InvoiceType: string
{
    case CreditTopUp = 'credit_top_up';
    case EvcBundle = 'evc_bundle';
    case Product = 'product';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::CreditTopUp => 'Credit Top-Up',
            self::EvcBundle => 'EVC Bundle',
            self::Product => 'Product',
            self::Manual => 'Manual',
        };
    }
}

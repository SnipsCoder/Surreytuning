<?php

namespace App\Enums;

enum ProductPaymentType: string
{
    case SlaveCredits = 'slave_credits';
    case DirectPayment = 'direct_payment';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::SlaveCredits => 'Slave Credits',
            self::DirectPayment => 'Direct Payment',
            self::Both => 'Both',
        };
    }
}

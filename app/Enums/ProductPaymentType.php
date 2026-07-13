<?php

namespace App\Enums;

enum ProductPaymentType: string
{
    case FileCredits = 'file_credits';
    case DirectPayment = 'direct_payment';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::FileCredits => 'File Credits',
            self::DirectPayment => 'Direct Payment',
            self::Both => 'Both',
        };
    }
}

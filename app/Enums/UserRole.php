<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Technician = 'technician';
    case Tuner = 'tuner';
    case DealerOwner = 'dealer_owner';
    case DealerUser = 'dealer_user';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Technician => 'Technician',
            self::Tuner => 'Tuner',
            self::DealerOwner => 'Dealer Owner',
            self::DealerUser => 'Dealer User',
        };
    }
}

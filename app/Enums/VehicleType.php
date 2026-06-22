<?php

namespace App\Enums;

enum VehicleType: string
{
    case All = 'all';
    case Car = 'car';
    case Van = 'van';
    case Bike = 'bike';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Car => 'Car',
            self::Van => 'Van',
            self::Bike => 'Bike',
            self::Other => 'Other',
        };
    }
}

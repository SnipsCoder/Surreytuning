<?php

namespace App\Enums;

enum TransmissionType: string
{
    case Manual = 'manual';
    case SemiAuto = 'semi_auto';
    case Automatic = 'automatic';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::SemiAuto => 'Semi-Automatic',
            self::Automatic => 'Automatic',
        };
    }
}

<?php

namespace App\Enums;

enum TuningToolCategory: string
{
    case Obd = 'obd';
    case Bench = 'bench';
    case Boot = 'boot';
    case Bdm = 'bdm';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Obd => 'OBD',
            self::Bench => 'Bench',
            self::Boot => 'Boot',
            self::Bdm => 'BDM',
            self::Other => 'Other',
        };
    }
}

<?php

namespace App\Enums;

enum AttachmentType: string
{
    case Original = 'original';
    case Returned = 'returned';
    case Supporting = 'supporting';
    case Certificate = 'certificate';
    case Ini = 'ini';

    public function label(): string
    {
        return match ($this) {
            self::Original => 'Original',
            self::Returned => 'Returned',
            self::Supporting => 'Supporting',
            self::Certificate => 'Certificate',
            self::Ini => 'INI',
        };
    }
}

<?php

namespace App\Enums;

enum PortalStatusEnum: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Delayed = 'delayed';
    case SupportOnly = 'support_only';
    case FilesOnly = 'files_only';
    case Closed = 'closed';
    case Noticeboard = 'noticeboard';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Busy => 'Busy',
            self::Delayed => 'Delayed',
            self::SupportOnly => 'Support Only',
            self::FilesOnly => 'Files Only',
            self::Closed => 'Closed',
            self::Noticeboard => 'Noticeboard',
        };
    }

    public function colour(): string
    {
        return match ($this) {
            self::Available => 'bg-green-100 text-green-800',
            self::Busy => 'bg-yellow-100 text-yellow-800',
            self::Delayed => 'bg-orange-100 text-orange-800',
            self::SupportOnly => 'bg-blue-100 text-blue-800',
            self::FilesOnly => 'bg-indigo-100 text-indigo-800',
            self::Closed => 'bg-red-100 text-red-800',
            self::Noticeboard => 'bg-purple-100 text-purple-800',
        };
    }
}

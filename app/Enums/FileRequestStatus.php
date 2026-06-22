<?php

namespace App\Enums;

enum FileRequestStatus: string
{
    case Pending = 'pending';
    case Progress = 'progress';
    case Responded = 'responded';
    case OnHold = 'on_hold';
    case RequiresSupport = 'requires_support';
    case Returned = 'returned';
    case Closed = 'closed';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Progress => 'In Progress',
            self::Responded => 'Responded',
            self::OnHold => 'On Hold',
            self::RequiresSupport => 'Requires Support',
            self::Returned => 'Returned',
            self::Closed => 'Closed',
            self::Void => 'Void',
        };
    }

    public function colour(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Progress => 'bg-blue-100 text-blue-800',
            self::Responded => 'bg-indigo-100 text-indigo-800',
            self::OnHold => 'bg-orange-100 text-orange-800',
            self::RequiresSupport => 'bg-red-100 text-red-800',
            self::Returned => 'bg-purple-100 text-purple-800',
            self::Closed => 'bg-green-100 text-green-800',
            self::Void => 'bg-gray-100 text-gray-800',
        };
    }
}

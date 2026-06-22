<?php

namespace App\Enums;

enum MessageType: string
{
    case Message = 'message';
    case System = 'system';
    case InternalNote = 'internal_note';
    case ChargeEvent = 'charge_event';
    case CreditEvent = 'credit_event';

    public function label(): string
    {
        return match ($this) {
            self::Message => 'Message',
            self::System => 'System',
            self::InternalNote => 'Internal Note',
            self::ChargeEvent => 'Charge Event',
            self::CreditEvent => 'Credit Event',
        };
    }
}

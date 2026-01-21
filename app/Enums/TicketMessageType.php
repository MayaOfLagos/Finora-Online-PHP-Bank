<?php

namespace App\Enums;

enum TicketMessageType: string
{
    case Customer = 'customer';
    case Agent = 'agent';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Agent => 'Agent',
            self::System => 'System',
        };
    }
}

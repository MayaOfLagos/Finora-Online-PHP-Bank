<?php

namespace App\Enums;

enum GrantStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Upcoming = 'upcoming';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Upcoming => 'Upcoming',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'gray',
            self::Upcoming => 'info',
        };
    }
}

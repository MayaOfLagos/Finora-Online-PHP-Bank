<?php

namespace App\Enums;

enum CardStatus: string
{
    case Active = 'active';
    case Frozen = 'frozen';
    case Blocked = 'blocked';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Frozen => 'Frozen',
            self::Blocked => 'Blocked',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Frozen => 'info',
            self::Blocked => 'danger',
            self::Expired => 'gray',
        };
    }
}

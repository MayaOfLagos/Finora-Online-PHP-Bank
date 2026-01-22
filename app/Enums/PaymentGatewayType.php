<?php

namespace App\Enums;

enum PaymentGatewayType: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';
    case CRYPTO = 'crypto';

    public function getLabel(): string
    {
        return match ($this) {
            self::AUTOMATIC => 'Automatic (API)',
            self::MANUAL => 'Manual',
            self::CRYPTO => 'Cryptocurrency',
        };
    }
}

<?php

namespace App\Enums;

enum DocumentType: string
{
    case Passport = 'passport';
    case IdCard = 'id_card';
    case DriverLicense = 'driver_license';

    public function label(): string
    {
        return match ($this) {
            self::Passport => 'Passport',
            self::IdCard => 'National ID Card',
            self::DriverLicense => 'Driver\'s License',
        };
    }
}

<?php

namespace App\Enums;

enum TransferStep: string
{
    case Pin = 'pin';
    case Imf = 'imf';
    case Tax = 'tax';
    case Cot = 'cot';
    case Otp = 'otp';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pin => 'Transaction PIN',
            self::Imf => 'IMF Verification',
            self::Tax => 'Tax Clearance',
            self::Cot => 'COT Verification',
            self::Otp => 'Email OTP',
            self::Completed => 'Completed',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::Pin => 1,
            self::Imf => 2,
            self::Tax => 3,
            self::Cot => 4,
            self::Otp => 5,
            self::Completed => 6,
        };
    }
}

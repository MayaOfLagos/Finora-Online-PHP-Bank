<?php

namespace App\Enums;

enum VerificationCodeType: string
{
    case Imf = 'imf';
    case Tax = 'tax';
    case Cot = 'cot';

    public function label(): string
    {
        return match ($this) {
            self::Imf => 'IMF Code',
            self::Tax => 'Tax Code',
            self::Cot => 'COT Code',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Imf => 'International Monetary Fund Verification Code',
            self::Tax => 'Tax Clearance Verification Code',
            self::Cot => 'Cost of Transfer Verification Code',
        };
    }
}

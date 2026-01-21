<?php

namespace App\Enums;

enum CardTransactionType: string
{
    case Purchase = 'purchase';
    case Atm = 'atm';
    case Refund = 'refund';
    case Fee = 'fee';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Atm => 'ATM Withdrawal',
            self::Refund => 'Refund',
            self::Fee => 'Fee',
        };
    }
}

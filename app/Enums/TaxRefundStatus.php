<?php

namespace App\Enums;

enum TaxRefundStatus: string
{
    case Pending = 'pending';
    case IdentityVerification = 'identity_verification';
    case Processing = 'processing';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::IdentityVerification => 'Identity Verification',
            self::Processing => 'Processing',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::IdentityVerification => 'warning',
            self::Processing => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Completed => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::IdentityVerification => 'heroicon-o-identification',
            self::Processing => 'heroicon-o-arrow-path',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Completed => 'heroicon-o-check-badge',
        };
    }
}

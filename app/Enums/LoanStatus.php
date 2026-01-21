<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Disbursed = 'disbursed';
    case Active = 'active';
    case Closed = 'closed';
    case Defaulted = 'defaulted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Disbursed => 'Disbursed',
            self::Active => 'Active',
            self::Closed => 'Closed',
            self::Defaulted => 'Defaulted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::UnderReview => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Disbursed => 'success',
            self::Active => 'primary',
            self::Closed => 'gray',
            self::Defaulted => 'danger',
        };
    }
}

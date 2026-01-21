<?php

namespace App\Models;

use App\Enums\RepaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'reference_number',
        'amount',
        'principal_portion',
        'interest_portion',
        'penalty_amount',
        'due_date',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'principal_portion' => 'integer',
            'interest_portion' => 'integer',
            'penalty_amount' => 'integer',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => RepaymentStatus::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (LoanRepayment $repayment) {
            if (empty($repayment->reference_number)) {
                $repayment->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'LR' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getPrincipalPortionInDollarsAttribute(): float
    {
        return $this->principal_portion / 100;
    }

    public function getInterestPortionInDollarsAttribute(): float
    {
        return $this->interest_portion / 100;
    }

    public function getPenaltyAmountInDollarsAttribute(): float
    {
        return $this->penalty_amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}

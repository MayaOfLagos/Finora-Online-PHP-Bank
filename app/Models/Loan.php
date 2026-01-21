<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'loan_application_id',
        'user_id',
        'bank_account_id',
        'principal_amount',
        'outstanding_balance',
        'interest_rate',
        'monthly_payment',
        'next_payment_date',
        'final_payment_date',
        'status',
        'disbursed_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'principal_amount' => 'integer',
            'outstanding_balance' => 'integer',
            'interest_rate' => 'decimal:2',
            'monthly_payment' => 'integer',
            'next_payment_date' => 'date',
            'final_payment_date' => 'date',
            'status' => LoanStatus::class,
            'disbursed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Loan $loan) {
            if (empty($loan->uuid)) {
                $loan->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getPrincipalAmountInDollarsAttribute(): float
    {
        return $this->principal_amount / 100;
    }

    public function getOutstandingBalanceInDollarsAttribute(): float
    {
        return $this->outstanding_balance / 100;
    }

    public function getMonthlyPaymentInDollarsAttribute(): float
    {
        return $this->monthly_payment / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }
}

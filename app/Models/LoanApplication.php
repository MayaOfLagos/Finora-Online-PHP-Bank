<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'loan_type_id',
        'reference_number',
        'amount',
        'term_months',
        'interest_rate',
        'monthly_payment',
        'total_payable',
        'purpose',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'term_months' => 'integer',
            'interest_rate' => 'decimal:2',
            'monthly_payment' => 'integer',
            'total_payable' => 'integer',
            'status' => LoanStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (LoanApplication $application) {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
            if (empty($application->reference_number)) {
                $application->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'LA' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getMonthlyPaymentInDollarsAttribute(): float
    {
        return $this->monthly_payment / 100;
    }

    public function getTotalPayableInDollarsAttribute(): float
    {
        return $this->total_payable / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }
}

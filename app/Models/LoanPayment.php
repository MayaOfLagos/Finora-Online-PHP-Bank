<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'loan_id',
        'user_id',
        'bank_account_id',
        'amount',
        'payment_date',
        'currency',
        'payment_method',
        'payment_type',
        'asset',
        'tx_hash',
        'exchange_rate',
        'reference_number',
        'notes',
        'metadata',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_date' => 'date',
            'exchange_rate' => 'decimal:8',
            'metadata' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (LoanPayment $payment) {
            if (empty($payment->uuid)) {
                $payment->uuid = (string) Str::uuid();
            }
            if (empty($payment->reference_number)) {
                $payment->reference_number = 'LP'.date('Ymd').strtoupper(Str::random(6));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

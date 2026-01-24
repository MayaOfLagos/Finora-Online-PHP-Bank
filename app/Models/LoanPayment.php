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
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_date' => 'date',
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
}

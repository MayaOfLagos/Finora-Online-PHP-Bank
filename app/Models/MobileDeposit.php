<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MobileDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'reference_number',
        'gateway',
        'gateway_transaction_id',
        'amount',
        'currency',
        'fee',
        'status',
        'gateway_response',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'fee' => 'integer',
            'status' => DepositStatus::class,
            'gateway_response' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (MobileDeposit $deposit) {
            if (empty($deposit->uuid)) {
                $deposit->uuid = (string) Str::uuid();
            }
            if (empty($deposit->reference_number)) {
                $deposit->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'MD'.date('Ymd').strtoupper(Str::random(8));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getFeeInDollarsAttribute(): float
    {
        return $this->fee / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}

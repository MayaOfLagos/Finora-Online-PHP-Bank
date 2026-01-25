<?php

namespace App\Models;

use App\Enums\TransferStatus;
use App\Enums\TransferStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WireTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'reference_number',
        'beneficiary_name',
        'beneficiary_account',
        'beneficiary_bank_name',
        'beneficiary_bank_address',
        'beneficiary_country',
        'beneficiary_data',
        'swift_code',
        'routing_number',
        'amount',
        'currency',
        'exchange_rate',
        'fee',
        'total_amount',
        'remarks',
        'status',
        'current_step',
        'otp_code',
        'otp_expires_at',
        'pin_verified_at',
        'imf_verified_at',
        'tax_verified_at',
        'cot_verified_at',
        'otp_verified_at',
        'completed_at',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'exchange_rate' => 'decimal:6',
            'fee' => 'integer',
            'total_amount' => 'integer',
            'beneficiary_data' => 'array',
            'status' => TransferStatus::class,
            'current_step' => TransferStep::class,
            'pin_verified_at' => 'datetime',
            'imf_verified_at' => 'datetime',
            'tax_verified_at' => 'datetime',
            'cot_verified_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (WireTransfer $transfer) {
            if (empty($transfer->uuid)) {
                $transfer->uuid = (string) Str::uuid();
            }
            if (empty($transfer->reference_number)) {
                $transfer->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'WT'.date('Ymd').strtoupper(Str::random(8));
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

    public function getTotalAmountInDollarsAttribute(): float
    {
        return $this->total_amount / 100;
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

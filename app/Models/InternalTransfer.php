<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InternalTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'sender_id',
        'sender_account_id',
        'receiver_id',
        'receiver_account_id',
        'beneficiary_data',
        'reference_number',
        'amount',
        'currency',
        'description',
        'status',
        'pin_verified_at',
        'otp_verified_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'beneficiary_data' => 'array',
            'status' => TransferStatus::class,
            'pin_verified_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (InternalTransfer $transfer) {
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
        return 'IT' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'sender_account_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function receiverAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'receiver_account_id');
    }
}

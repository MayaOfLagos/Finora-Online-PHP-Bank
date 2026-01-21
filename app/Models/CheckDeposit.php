<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CheckDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'reference_number',
        'check_number',
        'check_front_image',
        'check_back_image',
        'amount',
        'currency',
        'status',
        'rejection_reason',
        'hold_until',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => DepositStatus::class,
            'hold_until' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CheckDeposit $deposit) {
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
        return 'CD' . date('Ymd') . strtoupper(Str::random(8));
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

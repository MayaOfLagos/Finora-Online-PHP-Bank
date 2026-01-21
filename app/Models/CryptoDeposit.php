<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CryptoDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'cryptocurrency_id',
        'crypto_wallet_id',
        'reference_number',
        'crypto_amount',
        'usd_amount',
        'transaction_hash',
        'status',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'crypto_amount' => 'decimal:8',
            'usd_amount' => 'integer',
            'status' => DepositStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CryptoDeposit $deposit) {
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
        return 'CR' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getUsdAmountInDollarsAttribute(): float
    {
        return $this->usd_amount / 100;
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

    public function cryptocurrency(): BelongsTo
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function cryptoWallet(): BelongsTo
    {
        return $this->belongsTo(CryptoWallet::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

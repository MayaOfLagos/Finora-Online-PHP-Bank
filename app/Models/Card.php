<?php

namespace App\Models;

use App\Enums\CardStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'card_type_id',
        'card_number',
        'card_holder_name',
        'expiry_month',
        'expiry_year',
        'cvv',
        'pin',
        'spending_limit',
        'daily_limit',
        'status',
        'is_virtual',
        'issued_at',
        'expires_at',
        'blocked_at',
    ];

    protected $hidden = [
        'card_number',
        'expiry_month',
        'expiry_year',
        'cvv',
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'card_number' => 'encrypted',
            'expiry_month' => 'encrypted',
            'expiry_year' => 'encrypted',
            'cvv' => 'encrypted',
            'pin' => 'hashed',
            'spending_limit' => 'integer',
            'daily_limit' => 'integer',
            'status' => CardStatus::class,
            'is_virtual' => 'boolean',
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Card $card) {
            if (empty($card->uuid)) {
                $card->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get masked card number (last 4 digits).
     */
    public function getMaskedCardNumberAttribute(): string
    {
        $number = $this->card_number;

        return '**** **** **** ' . substr($number, -4);
    }

    public function getSpendingLimitInDollarsAttribute(): ?float
    {
        return $this->spending_limit ? $this->spending_limit / 100 : null;
    }

    public function getDailyLimitInDollarsAttribute(): ?float
    {
        return $this->daily_limit ? $this->daily_limit / 100 : null;
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

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }
}

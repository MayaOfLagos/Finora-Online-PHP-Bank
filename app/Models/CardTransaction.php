<?php

namespace App\Models;

use App\Enums\CardTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'reference_number',
        'merchant_name',
        'merchant_category',
        'amount',
        'currency',
        'type',
        'status',
        'transaction_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'type' => CardTransactionType::class,
            'transaction_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CardTransaction $transaction) {
            if (empty($transaction->reference_number)) {
                $transaction->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'CTX' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}

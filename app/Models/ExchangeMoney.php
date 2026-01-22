<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeMoney extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'exchange_money';

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'reference_number',
        'from_currency',
        'to_currency',
        'from_amount',
        'to_amount',
        'exchange_rate',
        'fee',
        'status',
        'completed_at',
        'notes',
        'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_amount' => 'integer',
            'to_amount' => 'integer',
            'exchange_rate' => 'decimal:8',
            'fee' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get formatted from amount (in dollars)
     */
    public function getFormattedFromAmountAttribute(): string
    {
        return number_format($this->from_amount / 100, 2);
    }

    /**
     * Get formatted to amount (in dollars)
     */
    public function getFormattedToAmountAttribute(): string
    {
        return number_format($this->to_amount / 100, 2);
    }

    /**
     * Generate a unique reference number
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'EXC'.date('Ymd').strtoupper(substr(uniqid(), -6));
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }
}

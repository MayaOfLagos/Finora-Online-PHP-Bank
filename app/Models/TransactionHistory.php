<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TransactionHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'transaction_type',
        'reference_number',
        'transactionable_type',
        'transactionable_id',
        'amount',
        'type',
        'balance_after',
        'currency',
        'status',
        'description',
        'generated_by',
        'email_sent',
        'wallet_debited',
        'metadata',
        'processed_at',
        'email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'email_sent' => 'boolean',
            'wallet_debited' => 'boolean',
            'metadata' => 'array',
            'processed_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'status' => TransactionStatus::class,
            'transaction_type' => TransactionType::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TransactionHistory $transactionHistory) {
            if (empty($transactionHistory->uuid)) {
                $transactionHistory->uuid = (string) Str::uuid();
            }
            if (empty($transactionHistory->reference_number)) {
                $transactionHistory->reference_number = strtoupper(Str::random(15));
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
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

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}

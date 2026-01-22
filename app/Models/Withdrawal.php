<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    /** @use HasFactory<\Database\Factories\WithdrawalFactory> */
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'bank_account_id',
        'reference_number',
        'amount',
        'currency',
        'status',
        'reason',
        'bank_details',
        'approved_at',
        'completed_at',
        'rejection_reason',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'bank_details' => 'json',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Withdrawal $withdrawal) {
            if (empty($withdrawal->id)) {
                $withdrawal->id = Str::uuid();
            }
            if (empty($withdrawal->reference_number)) {
                $withdrawal->reference_number = 'WTH'.date('Ymd').strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}

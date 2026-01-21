<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'beneficiary_user_id',
        'beneficiary_account_id',
        'nickname',
        'is_verified',
        'is_favorite',
        'transfer_limit',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_favorite' => 'boolean',
            'transfer_limit' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Beneficiary $beneficiary) {
            if (empty($beneficiary->uuid)) {
                $beneficiary->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getTransferLimitInDollarsAttribute(): ?float
    {
        return $this->transfer_limit ? $this->transfer_limit / 100 : null;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beneficiaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }

    public function beneficiaryAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'beneficiary_account_id');
    }
}

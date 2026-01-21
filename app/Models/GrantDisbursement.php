<?php

namespace App\Models;

use App\Enums\DepositStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GrantDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'grant_application_id',
        'bank_account_id',
        'reference_number',
        'amount',
        'status',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => DepositStatus::class,
            'disbursed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GrantDisbursement $disbursement) {
            if (empty($disbursement->reference_number)) {
                $disbursement->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'GD' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function grantApplication(): BelongsTo
    {
        return $this->belongsTo(GrantApplication::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}

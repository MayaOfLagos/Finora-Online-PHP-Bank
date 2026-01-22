<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TaxRefund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'bank_account_id',
        'reference_number',
        'tax_year',
        'ssn_tin',
        'filing_status',
        'refund_amount',
        'currency',
        'irs_reference_number',
        'status',
        'rejection_reason',
        'submitted_at',
        'processed_at',
        'approved_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'processed_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TaxRefund $taxRefund) {
            if (empty($taxRefund->uuid)) {
                $taxRefund->uuid = (string) Str::uuid();
            }
            if (empty($taxRefund->reference_number)) {
                $taxRefund->reference_number = 'TAXREF-'.strtoupper(Str::random(10));
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'min_amount',
        'max_amount',
        'min_term_months',
        'max_term_months',
        'interest_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'integer',
            'max_amount' => 'integer',
            'min_term_months' => 'integer',
            'max_term_months' => 'integer',
            'interest_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getMinAmountInDollarsAttribute(): float
    {
        return $this->min_amount / 100;
    }

    public function getMaxAmountInDollarsAttribute(): float
    {
        return $this->max_amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function applications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_balance' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the minimum balance in dollars.
     */
    public function getMinimumBalanceInDollarsAttribute(): float
    {
        return $this->minimum_balance / 100;
    }

    /**
     * Get all bank accounts of this type.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }
}

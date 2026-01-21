<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_virtual',
        'is_credit',
        'default_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_virtual' => 'boolean',
            'is_credit' => 'boolean',
            'default_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getDefaultLimitInDollarsAttribute(): ?float
    {
        return $this->default_limit ? $this->default_limit / 100 : null;
    }

    // ==================== RELATIONSHIPS ====================

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(CardRequest::class);
    }
}

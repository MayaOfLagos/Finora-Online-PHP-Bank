<?php

namespace App\Models;

use App\Enums\GrantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrantProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'currency',
        'eligibility_criteria',
        'required_documents',
        'start_date',
        'end_date',
        'max_recipients',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'eligibility_criteria' => 'array',
            'required_documents' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'max_recipients' => 'integer',
            'status' => GrantStatus::class,
        ];
    }

    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    // ==================== RELATIONSHIPS ====================

    public function applications(): HasMany
    {
        return $this->hasMany(GrantApplication::class);
    }
}

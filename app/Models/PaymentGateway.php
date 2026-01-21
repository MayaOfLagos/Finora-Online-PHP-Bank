<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'credentials',
        'is_active',
        'is_test_mode',
    ];

    protected $hidden = [
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
        ];
    }

    /**
     * Get a specific credential.
     */
    public function getCredential(string $key): ?string
    {
        return $this->credentials[$key] ?? null;
    }
}

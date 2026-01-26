<?php

namespace App\Models;

use App\Enums\PaymentGatewayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'logo',
        'description',
        'currency',
        'credentials',
        'settings',
        'is_active',
        'is_test_mode',
    ];

    protected $hidden = [
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentGatewayType::class,
            'credentials' => 'array',
            'settings' => 'array',
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

    /**
     * Get a specific setting.
     */
    public function getSetting(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }
}

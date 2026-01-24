<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryFieldTemplate extends Model
{
    protected $fillable = [
        'field_key',
        'field_label',
        'field_type',
        'is_required',
        'is_enabled',
        'applies_to',
        'options',
        'display_order',
        'placeholder',
        'helper_text',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_enabled' => 'boolean',
            'options' => 'array',
            'display_order' => 'integer',
        ];
    }

    /**
     * Get fields for specific transfer type
     */
    public static function forTransferType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_enabled', true)
            ->where(function ($query) use ($type) {
                $query->where('applies_to', $type)
                    ->orWhere('applies_to', 'all');
            })
            ->orderBy('display_order')
            ->get();
    }
}


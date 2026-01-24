<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KycDocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'instructions',
        'is_required',
        'requires_front_image',
        'requires_back_image',
        'requires_selfie',
        'requires_document_number',
        'accepted_formats',
        'max_file_size',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'requires_front_image' => 'boolean',
            'requires_back_image' => 'boolean',
            'requires_selfie' => 'boolean',
            'requires_document_number' => 'boolean',
            'accepted_formats' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // ==================== BOOT ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class, 'template_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    public function getAcceptedFormatsListAttribute(): string
    {
        if (empty($this->accepted_formats)) {
            return 'jpg, png, pdf';
        }

        return implode(', ', $this->accepted_formats);
    }

    public function getMaxFileSizeForHumansAttribute(): string
    {
        if ($this->max_file_size >= 1024) {
            return round($this->max_file_size / 1024, 1).' MB';
        }

        return $this->max_file_size.' KB';
    }

    public function getRequirementsListAttribute(): array
    {
        $requirements = [];

        if ($this->requires_front_image) {
            $requirements[] = 'Front image';
        }
        if ($this->requires_back_image) {
            $requirements[] = 'Back image';
        }
        if ($this->requires_selfie) {
            $requirements[] = 'Selfie with document';
        }
        if ($this->requires_document_number) {
            $requirements[] = 'Document number';
        }

        return $requirements;
    }
}

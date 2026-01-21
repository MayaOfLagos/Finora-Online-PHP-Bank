<?php

namespace App\Models;

use App\Enums\GrantApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class GrantApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'grant_program_id',
        'reference_number',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => GrantApplicationStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GrantApplication $application) {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
            if (empty($application->reference_number)) {
                $application->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'GA' . date('Ymd') . strtoupper(Str::random(8));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantProgram(): BelongsTo
    {
        return $this->belongsTo(GrantProgram::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GrantDocument::class);
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(GrantDisbursement::class);
    }
}

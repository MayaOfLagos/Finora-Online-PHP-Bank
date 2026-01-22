<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Reward extends Model
{
    /** @use HasFactory<\Database\Factories\RewardFactory> */
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'description',
        'points',
        'type',
        'status',
        'earned_date',
        'expiry_date',
        'redeemed_at',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'earned_date' => 'date',
            'expiry_date' => 'date',
            'redeemed_at' => 'datetime',
            'metadata' => 'json',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Reward $reward) {
            if (empty($reward->id)) {
                $reward->id = Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

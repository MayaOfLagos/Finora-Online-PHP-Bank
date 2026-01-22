<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\VoucherFactory> */
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'code',
        'description',
        'amount',
        'currency',
        'type',
        'status',
        'usage_limit',
        'times_used',
        'is_used',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expires_at' => 'date',
            'used_at' => 'datetime',
            'metadata' => 'json',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->id)) {
                $voucher->id = Str::uuid();
            }
            if (empty($voucher->code)) {
                $voucher->code = strtoupper(Str::random(10));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

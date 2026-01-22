<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MoneyRequest extends Model
{
    /** @use HasFactory<\Database\Factories\MoneyRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'money_requests';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'requester_id',
        'responder_id',
        'reference_number',
        'amount',
        'currency',
        'status',
        'reason',
        'accepted_at',
        'completed_at',
        'rejected_at',
        'rejection_reason',
        'expires_at',
        'type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expires_at' => 'date',
            'metadata' => 'json',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (MoneyRequest $request) {
            if (empty($request->id)) {
                $request->id = Str::uuid();
            }
            if (empty($request->reference_number)) {
                $request->reference_number = 'MRQ'.date('Ymd').strtoupper(Str::random(8));
            }
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}

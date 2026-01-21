<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrantDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'grant_application_id',
        'document_type',
        'file_path',
        'original_name',
    ];

    // ==================== RELATIONSHIPS ====================

    public function grantApplication(): BelongsTo
    {
        return $this->belongsTo(GrantApplication::class);
    }
}

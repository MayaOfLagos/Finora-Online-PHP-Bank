<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\KycStatus;
use App\Mail\KycApprovedMail;
use App\Mail\KycRejectedMail;
use App\Mail\KycSubmittedMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class KycVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'document_type',
        'document_number',
        'document_front_path',
        'document_back_path',
        'selfie_path',
        'address_proof_path',
        'status',
        'rejection_reason',
        'admin_notes',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => KycStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(KycDocumentTemplate::class, 'template_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', KycStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', KycStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', KycStatus::Rejected);
    }

    // ==================== ACCESSORS ====================

    public function getDocumentTypeNameAttribute(): string
    {
        if ($this->template) {
            return $this->template->name;
        }

        return $this->document_type?->label() ?? 'Unknown';
    }

    public function getHasDocumentFrontAttribute(): bool
    {
        return ! empty($this->document_front_path);
    }

    public function getHasDocumentBackAttribute(): bool
    {
        return ! empty($this->document_back_path);
    }

    public function getHasSelfieAttribute(): bool
    {
        return ! empty($this->selfie_path);
    }

    public function getHasAddressProofAttribute(): bool
    {
        return ! empty($this->address_proof_path);
    }

    // ==================== ACTIONS ====================

    public function approve(?int $verifierId = null, ?string $notes = null): bool
    {
        $this->status = KycStatus::Approved;
        $this->verified_at = now();
        $this->verified_by = $verifierId ?? auth()->id();
        $this->admin_notes = $notes;
        $this->rejection_reason = null;

        $saved = $this->save();

        if ($saved) {
            // Update user's KYC status
            $this->user->update([
                'is_verified' => true,
                'kyc_level' => max($this->user->kyc_level ?? 0, 1),
            ]);

            // Send approval email
            $this->sendApprovalEmail();
        }

        return $saved;
    }

    public function reject(?int $verifierId = null, ?string $reason = null, ?string $notes = null): bool
    {
        $this->status = KycStatus::Rejected;
        $this->verified_at = now();
        $this->verified_by = $verifierId ?? auth()->id();
        $this->rejection_reason = $reason;
        $this->admin_notes = $notes;

        $saved = $this->save();

        if ($saved) {
            // Send rejection email
            $this->sendRejectionEmail();
        }

        return $saved;
    }

    // ==================== EMAIL NOTIFICATIONS ====================

    public function sendSubmissionEmail(): void
    {
        if ($this->user && $this->user->email) {
            Mail::to($this->user->email)->queue(new KycSubmittedMail($this));
        }
    }

    public function sendApprovalEmail(): void
    {
        if ($this->user && $this->user->email) {
            Mail::to($this->user->email)->queue(new KycApprovedMail($this));
        }
    }

    public function sendRejectionEmail(): void
    {
        if ($this->user && $this->user->email) {
            Mail::to($this->user->email)->queue(new KycRejectedMail($this));
        }
    }
}

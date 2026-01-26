<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, HasEmailAuthentication, HasName, MustVerifyEmail
{
    use HasFactory;
    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use InteractsWithEmailAuthentication;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'middle_name',
        'username',
        'email',
        'password',
        'phone_number',
        'date_of_birth',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'transaction_pin',
        'is_active',
        'is_verified',
        'kyc_level',
        'two_factor_enabled',
        'two_factor_secret',
        'profile_photo_path',
        'can_transfer',
        'can_withdraw',
        'can_deposit',
        'can_request_loan',
        'can_request_card',
        'can_apply_grant',
        'can_send_wire_transfer',
        'can_send_internal_transfer',
        'can_send_domestic_transfer',
        'can_create_beneficiary',
        // Wire transfer verification codes
        'imf_code',
        'tax_code',
        'cot_code',
        // User-level OTP override
        'skip_transfer_otp',
        'skip_email_otp',
        // Filament MFA columns
        'app_authentication_secret',
        'app_authentication_recovery_codes',
        'has_email_authentication',
        // Login tracking
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
        'two_factor_secret',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_otp_verified_at' => 'datetime',
            'pin_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'transaction_pin' => 'hashed',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'kyc_level' => 'integer',
            'two_factor_enabled' => 'boolean',
            'can_transfer' => 'boolean',
            'can_withdraw' => 'boolean',
            'can_deposit' => 'boolean',
            'can_request_loan' => 'boolean',
            'can_request_card' => 'boolean',
            'can_apply_grant' => 'boolean',
            'can_send_wire_transfer' => 'boolean',
            'can_send_internal_transfer' => 'boolean',
            'can_send_domestic_transfer' => 'boolean',
            'can_create_beneficiary' => 'boolean',
            'skip_transfer_otp' => 'boolean',
            'has_email_authentication' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    // ==================== FILAMENT INTERFACE METHODS ====================

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // For now, all active verified users can access the admin panel
        // You can add role-based access control here
        return $this->is_active && $this->hasVerifiedEmail();
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::url($this->profile_photo_path);
        }

        return null;
    }

    /**
     * Get the user's name for Filament.
     */
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get name attribute for Filament compatibility.
     */
    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    // ==================== RELATIONSHIPS ====================

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function kycVerifications(): HasMany
    {
        return $this->hasMany(KycVerification::class);
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function wireTransfers(): HasMany
    {
        return $this->hasMany(WireTransfer::class);
    }

    public function sentInternalTransfers(): HasMany
    {
        return $this->hasMany(InternalTransfer::class, 'sender_id');
    }

    public function receivedInternalTransfers(): HasMany
    {
        return $this->hasMany(InternalTransfer::class, 'receiver_id');
    }

    public function domesticTransfers(): HasMany
    {
        return $this->hasMany(DomesticTransfer::class);
    }

    public function accountTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class);
    }

    public function checkDeposits(): HasMany
    {
        return $this->hasMany(CheckDeposit::class);
    }

    public function mobileDeposits(): HasMany
    {
        return $this->hasMany(MobileDeposit::class);
    }

    public function cryptoDeposits(): HasMany
    {
        return $this->hasMany(CryptoDeposit::class);
    }

    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function cardRequests(): HasMany
    {
        return $this->hasMany(CardRequest::class);
    }

    public function grantApplications(): HasMany
    {
        return $this->hasMany(GrantApplication::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function ticketMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function notificationSettings(): HasMany
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function taxRefunds(): HasMany
    {
        return $this->hasMany(TaxRefund::class);
    }

    public function transactionHistories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function moneyRequestsSent(): HasMany
    {
        return $this->hasMany(MoneyRequest::class, 'requester_id');
    }

    public function moneyRequestsReceived(): HasMany
    {
        return $this->hasMany(MoneyRequest::class, 'responder_id');
    }

    public function exchangeMoney(): HasMany
    {
        return $this->hasMany(ExchangeMoney::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if transfer OTP verification is required for this user.
     * Returns false if user has skip_transfer_otp enabled OR if global setting is disabled.
     */
    public function requiresTransferOtp(): bool
    {
        // If user has skip_transfer_otp enabled, they don't need OTP
        if ($this->skip_transfer_otp) {
            return false;
        }

        // Otherwise, check global setting
        return (bool) Setting::getValue('security', 'transfer_otp_enabled', true);
    }

    /**
     * Check if user has wire transfer verification codes set.
     */
    public function hasTransferCodes(): bool
    {
        return ! empty($this->imf_code) || ! empty($this->tax_code) || ! empty($this->cot_code);
    }

    /**
     * Get the required transfer codes for wire transfer verification.
     * Returns array of code types that are set for this user.
     */
    public function getRequiredTransferCodes(): array
    {
        $codes = [];

        if (! empty($this->imf_code)) {
            $codes[] = 'imf';
        }
        if (! empty($this->tax_code)) {
            $codes[] = 'tax';
        }
        if (! empty($this->cot_code)) {
            $codes[] = 'cot';
        }

        return $codes;
    }

    // ==================== AUTH & NOTIFICATIONS ====================

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
    }
}

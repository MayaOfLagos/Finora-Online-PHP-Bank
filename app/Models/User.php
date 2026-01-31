<?php

namespace App\Models;

use App\Enums\UserRole;
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        // Preferences
        'theme',
        'language',
        'currency_display',
        'date_format',
        'time_format',
        'timezone',
        // Notification preferences
        'notify_email_transactions',
        'notify_email_security',
        'notify_email_marketing',
        'notify_push_transactions',
        'notify_push_security',
        'notify_sms_transactions',
        'notify_sms_security',
        // Lockscreen
        'lockscreen_enabled',
        'lockscreen_timeout',
        'role',
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
        // User preferences
        'theme',
        'language',
        'currency_display',
        'date_format',
        'time_format',
        'timezone',
        // Notification preferences
        'notify_email_transactions',
        'notify_email_security',
        'notify_email_marketing',
        'notify_push_transactions',
        'notify_push_security',
        'notify_sms_transactions',
        'notify_sms_security',
        // Lockscreen settings
        'lockscreen_enabled',
        'lockscreen_timeout',
        // Referral fields
        'referral_code',
        'referred_by',
        'referred_at',
        'total_referral_earnings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
        'two_factor_secret',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
        'full_name',
        'initials',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
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
            'notify_email_transactions' => 'boolean',
            'notify_email_security' => 'boolean',
            'notify_email_marketing' => 'boolean',
            'notify_push_transactions' => 'boolean',
            'notify_push_security' => 'boolean',
            'notify_sms_transactions' => 'boolean',
            'notify_sms_security' => 'boolean',
            'lockscreen_enabled' => 'boolean',
            'lockscreen_timeout' => 'integer',
            'referred_at' => 'datetime',
            'total_referral_earnings' => 'integer',
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
        // Only staff, admin, and super_admin can access the admin panel
        return $this->is_active
            && $this->hasVerifiedEmail()
            && $this->role?->canAccessAdmin();
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /**
     * Check if user is an admin or higher.
     */
    public function isAdmin(): bool
    {
        return $this->role?->isAdminOrHigher() ?? false;
    }

    /**
     * Check if user is staff or higher.
     */
    public function isStaff(): bool
    {
        return $this->role?->canAccessAdmin() ?? false;
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

    /**
     * Get user initials (first letter of first name + first letter of last name).
     */
    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_name ? mb_strtoupper(mb_substr($this->first_name, 0, 1)) : '';
        $lastInitial = $this->last_name ? mb_strtoupper(mb_substr($this->last_name, 0, 1)) : '';

        return $firstInitial.$lastInitial ?: 'U';
    }

    /**
     * Get the user's avatar URL.
     * Returns the uploaded profile photo URL if exists, otherwise returns a UI Avatars fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        // If user has uploaded a profile photo, return its URL
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::url($this->profile_photo_path);
        }

        // Generate fallback avatar using UI Avatars service
        return $this->generateFallbackAvatarUrl();
    }

    /**
     * Check if user has a custom profile photo uploaded.
     */
    public function hasProfilePhoto(): bool
    {
        return $this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path);
    }

    /**
     * Generate a fallback avatar URL using UI Avatars service.
     * This creates a nice looking avatar with the user's initials.
     */
    protected function generateFallbackAvatarUrl(): string
    {
        $name = urlencode($this->full_name ?: 'User');
        $background = '6366f1'; // Indigo-500 to match app theme
        $color = 'ffffff'; // White text
        $size = 128; // Good size for retina displays

        return "https://ui-avatars.com/api/?name={$name}&background={$background}&color={$color}&size={$size}&bold=true&format=svg";
    }

    /**
     * Get the profile photo URL (alias for avatar_url for backward compatibility).
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->avatar_url;
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

    // ==================== REFERRAL RELATIONSHIPS ====================

    /**
     * Get the user who referred this user.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get users that this user has referred (direct relationship).
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get referral records where this user is the referrer.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get the referral record where this user was referred.
     */
    public function referralRecord(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
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
     * Get the user's primary bank account.
     */
    public function getPrimaryBankAccount(): ?BankAccount
    {
        return $this->bankAccounts()->where('is_primary', true)->first()
            ?? $this->bankAccounts()->first();
    }

    /**
     * Get the user's primary currency (from primary bank account).
     * Falls back to 'USD' if no bank account exists.
     */
    public function getPrimaryCurrency(): string
    {
        $primaryAccount = $this->getPrimaryBankAccount();

        return $primaryAccount?->currency ?? 'USD';
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

    // ==================== REFERRAL METHODS ====================

    /**
     * Generate a unique referral code for the user.
     */
    public function generateReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Get the user's referral URL.
     */
    public function getReferralUrlAttribute(): string
    {
        $code = $this->referral_code ?? $this->generateReferralCode();

        return url('/register?ref='.$code);
    }

    /**
     * Get total number of successful referrals.
     */
    public function getTotalReferralsAttribute(): int
    {
        return $this->referredUsers()->count();
    }

    /**
     * Get number of pending referrals.
     */
    public function getPendingReferralsAttribute(): int
    {
        return $this->referrals()->where('status', 'pending')->count();
    }

    /**
     * Get number of completed referrals.
     */
    public function getCompletedReferralsAttribute(): int
    {
        return $this->referrals()->whereIn('status', ['completed', 'rewarded'])->count();
    }

    /**
     * Get formatted total referral earnings.
     */
    public function getFormattedReferralEarningsAttribute(): string
    {
        return number_format(($this->total_referral_earnings ?? 0) / 100, 2);
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

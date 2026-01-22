<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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
}

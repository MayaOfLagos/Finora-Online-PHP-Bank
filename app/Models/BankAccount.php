<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'account_type_id',
        'account_number',
        'routing_number',
        'swift_code',
        'balance',
        'currency',
        'is_primary',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BankAccount $account) {
            if (empty($account->uuid)) {
                $account->uuid = (string) Str::uuid();
            }
            if (empty($account->account_number)) {
                $account->account_number = self::generateAccountNumber();
            }
            if (empty($account->routing_number)) {
                $account->routing_number = self::generateRoutingNumber();
            }
            if (empty($account->swift_code)) {
                $account->swift_code = self::generateSwiftCode();
            }
        });
    }

    /**
     * Generate a unique account number.
     */
    public static function generateAccountNumber(): string
    {
        do {
            $number = '1'.str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (self::where('account_number', $number)->exists());

        return $number;
    }

    /**
     * Generate a routing number (9 digits).
     */
    public static function generateRoutingNumber(): string
    {
        // Generate a 9-digit routing number
        // First 4 digits: Federal Reserve routing symbol (0100-1299)
        $prefix = str_pad(random_int(100, 1299), 4, '0', STR_PAD_LEFT);
        // Next 4 digits: ABA institution identifier
        $middle = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        // Last digit: check digit (simplified)
        $checkDigit = random_int(0, 9);

        return $prefix.$middle.$checkDigit;
    }

    /**
     * Generate a SWIFT/BIC code (8 or 11 characters).
     */
    public static function generateSwiftCode(): string
    {
        // SWIFT code format: AAAABBCCXXX
        // AAAA = Bank code (4 letters)
        // BB = Country code (2 letters)
        // CC = Location code (2 characters)
        // XXX = Branch code (3 characters, optional)

        $bankCode = 'FNRA'; // Finora Bank
        $countryCode = 'US';
        $locationCode = chr(random_int(65, 90)).random_int(0, 9); // Letter + digit
        $branchCode = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return $bankCode.$countryCode.$locationCode.$branchCode;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get balance in dollars.
     */
    public function getBalanceInDollarsAttribute(): float
    {
        return $this->balance / 100;
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance_in_dollars, 2);
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function transactionHistories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class);
    }

    public function wireTransfers(): HasMany
    {
        return $this->hasMany(WireTransfer::class);
    }

    public function sentInternalTransfers(): HasMany
    {
        return $this->hasMany(InternalTransfer::class, 'sender_account_id');
    }

    public function receivedInternalTransfers(): HasMany
    {
        return $this->hasMany(InternalTransfer::class, 'receiver_account_id');
    }

    public function domesticTransfers(): HasMany
    {
        return $this->hasMany(DomesticTransfer::class);
    }

    public function outgoingAccountTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function incomingAccountTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
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

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function grantDisbursements(): HasMany
    {
        return $this->hasMany(GrantDisbursement::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class, 'beneficiary_account_id');
    }

    public function taxRefunds(): HasMany
    {
        return $this->hasMany(TaxRefund::class);
    }
}

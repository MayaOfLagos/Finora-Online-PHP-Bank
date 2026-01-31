<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use App\Services\ActivityLogger;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class UserDetailsTabs extends Widget
{
    use WithPagination;

    protected string $view = 'filament.resources.users.widgets.user-details-tabs';

    #[Locked]
    public ?string $recordId = null;

    protected int|string|array $columnSpan = 'full';

    public int $transactionsPerPage = 15;

    public int $transactionsPage = 1;

    public function mount(Model|int|string|null $record = null): void
    {
        if ($record instanceof User) {
            $this->recordId = $record->uuid;
        } elseif (is_string($record)) {
            $this->recordId = $record;
        }
    }

    /**
     * Go to a specific transactions page.
     */
    public function goToTransactionsPage(int $page): void
    {
        $this->transactionsPage = max(1, $page);
    }

    /**
     * Go to previous transactions page.
     */
    public function previousTransactionsPage(): void
    {
        $this->transactionsPage = max(1, $this->transactionsPage - 1);
    }

    /**
     * Go to next transactions page.
     */
    public function nextTransactionsPage(): void
    {
        $this->transactionsPage++;
    }

    public function getRecord(): ?User
    {
        if (! $this->recordId) {
            return null;
        }

        return User::where('uuid', $this->recordId)->first();
    }

    #[Computed]
    public function record(): ?User
    {
        return $this->getRecord();
    }

    public function __get($name)
    {
        if ($name === 'record') {
            return $this->getRecord();
        }

        return parent::__get($name);
    }

    #[Computed]
    public function recentTransactions()
    {
        return $this->record->transactionHistories()
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function recentLogins()
    {
        return $this->record->loginHistories()
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function totalTransactions()
    {
        return $this->record->transactionHistories()->count();
    }

    #[Computed]
    public function totalBalance()
    {
        return $this->record->bankAccounts()->sum('balance') / 100;
    }

    #[Computed]
    public function kycVerifications()
    {
        return $this->record->kycVerifications()
            ->latest()
            ->get();
    }

    #[Computed]
    public function allTransactions()
    {
        return collect([
            ...$this->record->wireTransfers()->latest()->take(10)->get(),
            ...$this->record->sentInternalTransfers()->latest()->take(5)->get(),
            ...$this->record->domesticTransfers()->latest()->take(5)->get(),
            ...$this->record->accountTransfers()->latest()->take(5)->get(),
            ...$this->record->checkDeposits()->latest()->take(5)->get(),
            ...$this->record->mobileDeposits()->latest()->take(5)->get(),
            ...$this->record->cryptoDeposits()->latest()->take(5)->get(),
        ])
            ->sortByDesc('created_at')
            ->take(20);
    }

    /**
     * Get all transactions for the user with pagination support.
     * Returns a collection with metadata for manual pagination.
     */
    public function getPaginatedTransactions(int $page = 1): array
    {
        $perPage = $this->transactionsPerPage;

        // Collect all transactions with type metadata
        $allTransactions = collect();

        // Transaction Histories
        $this->record->transactionHistories()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Transaction History';
            $t->_category = 'admin';
            $allTransactions->push($t);
        });

        // Wire Transfers
        $this->record->wireTransfers()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Wire Transfer';
            $t->_category = 'transfer_out';
            $allTransactions->push($t);
        });

        // Sent Internal Transfers
        $this->record->sentInternalTransfers()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Internal Transfer (Sent)';
            $t->_category = 'transfer_out';
            $allTransactions->push($t);
        });

        // Domestic Transfers
        $this->record->domesticTransfers()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Domestic Transfer';
            $t->_category = 'transfer_out';
            $allTransactions->push($t);
        });

        // Account Transfers
        $this->record->accountTransfers()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Account Transfer';
            $t->_category = 'transfer_out';
            $allTransactions->push($t);
        });

        // Received Internal Transfers
        $this->record->receivedInternalTransfers()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Internal Transfer (Received)';
            $t->_category = 'transfer_in';
            $allTransactions->push($t);
        });

        // Check Deposits
        $this->record->checkDeposits()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Check Deposit';
            $t->_category = 'deposit';
            $allTransactions->push($t);
        });

        // Mobile Deposits
        $this->record->mobileDeposits()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Mobile Deposit';
            $t->_category = 'deposit';
            $allTransactions->push($t);
        });

        // Crypto Deposits
        $this->record->cryptoDeposits()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Crypto Deposit';
            $t->_category = 'deposit';
            $allTransactions->push($t);
        });

        // Withdrawals
        $this->record->withdrawals()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Withdrawal';
            $t->_category = 'withdrawal';
            $allTransactions->push($t);
        });

        // Money Requests Sent
        $this->record->moneyRequestsSent()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Money Request (Sent)';
            $t->_category = 'request';
            $allTransactions->push($t);
        });

        // Money Requests Received
        $this->record->moneyRequestsReceived()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Money Request (Received)';
            $t->_category = 'request';
            $allTransactions->push($t);
        });

        // Exchange Money
        $this->record->exchangeMoney()->get()->each(function ($t) use ($allTransactions) {
            $t->_type = 'Currency Exchange';
            $t->_category = 'exchange';
            $allTransactions->push($t);
        });

        // Sort by created_at descending
        $sorted = $allTransactions->sortByDesc('created_at')->values();

        // Calculate pagination
        $total = $sorted->count();
        $lastPage = (int) ceil($total / $perPage);
        $page = max(1, min($page, $lastPage ?: 1));
        $offset = ($page - 1) * $perPage;

        return [
            'data' => $sorted->slice($offset, $perPage)->values(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage ?: 1,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Get transaction counts for summary cards.
     */
    public function getTransactionCounts(): array
    {
        return [
            'wire' => $this->record->wireTransfers()->count(),
            'internal_sent' => $this->record->sentInternalTransfers()->count(),
            'internal_received' => $this->record->receivedInternalTransfers()->count(),
            'domestic' => $this->record->domesticTransfers()->count(),
            'account' => $this->record->accountTransfers()->count(),
            'check_deposit' => $this->record->checkDeposits()->count(),
            'mobile_deposit' => $this->record->mobileDeposits()->count(),
            'crypto_deposit' => $this->record->cryptoDeposits()->count(),
            'withdrawal' => $this->record->withdrawals()->count(),
            'money_request' => $this->record->moneyRequestsSent()->count() + $this->record->moneyRequestsReceived()->count(),
            'exchange' => $this->record->exchangeMoney()->count(),
            'history' => $this->record->transactionHistories()->count(),
        ];
    }

    #[Computed]
    public function wireTransfersStats()
    {
        return [
            'completed' => $this->record->wireTransfers()->where('status', 'completed')->count(),
            'pending' => $this->record->wireTransfers()->where('status', 'pending')->count(),
            'failed' => $this->record->wireTransfers()->where('status', 'failed')->count(),
            'total_amount' => $this->record->wireTransfers()->where('status', 'completed')->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function domesticTransfersStats()
    {
        return [
            'completed' => $this->record->domesticTransfers()->where('status', 'completed')->count(),
            'pending' => $this->record->domesticTransfers()->where('status', 'pending')->count(),
            'failed' => $this->record->domesticTransfers()->where('status', 'failed')->count(),
            'total_amount' => $this->record->domesticTransfers()->where('status', 'completed')->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function internalTransfersStats()
    {
        return [
            'completed' => $this->record->sentInternalTransfers()->where('status', 'completed')->count(),
            'pending' => $this->record->sentInternalTransfers()->where('status', 'pending')->count(),
            'failed' => $this->record->sentInternalTransfers()->where('status', 'failed')->count(),
            'total_amount' => $this->record->sentInternalTransfers()->where('status', 'completed')->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function totalTransfersStats()
    {
        $wire = $this->record->wireTransfers();
        $domestic = $this->record->domesticTransfers();
        $internal = $this->record->sentInternalTransfers();

        return [
            'completed' => $wire->where('status', 'completed')->count() + $domestic->where('status', 'completed')->count() + $internal->where('status', 'completed')->count(),
            'pending' => $wire->where('status', 'pending')->count() + $domestic->where('status', 'pending')->count() + $internal->where('status', 'pending')->count(),
            'failed' => $wire->where('status', 'failed')->count() + $domestic->where('status', 'failed')->count() + $internal->where('status', 'failed')->count(),
            'total_amount' => ($wire->where('status', 'completed')->sum('amount') + $domestic->where('status', 'completed')->sum('amount') + $internal->where('status', 'completed')->sum('amount')) / 100,
        ];
    }

    #[Computed]
    public function depositsStats()
    {
        return [
            'check' => $this->record->checkDeposits()->where('status', 'completed')->count(),
            'mobile' => $this->record->mobileDeposits()->where('status', 'completed')->count(),
            'crypto' => $this->record->cryptoDeposits()->where('status', 'completed')->count(),
            'total_amount' => ($this->record->checkDeposits()->where('status', 'completed')->sum('amount') + $this->record->mobileDeposits()->where('status', 'completed')->sum('amount') + $this->record->cryptoDeposits()->where('status', 'completed')->sum('usd_amount')) / 100,
        ];
    }

    #[Computed]
    public function monthlyTransactionStats()
    {
        $months = [];
        $completed = [];
        $pending = [];
        $failed = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');

            // Get transfers for this month
            $monthStart = $date->startOfMonth();
            $monthEnd = $date->endOfMonth();

            $completed[] = collect([
                ...$this->record->wireTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'completed')->get(),
                ...$this->record->domesticTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'completed')->get(),
                ...$this->record->sentInternalTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'completed')->get(),
            ])->count();

            $pending[] = collect([
                ...$this->record->wireTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'pending')->get(),
                ...$this->record->domesticTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'pending')->get(),
                ...$this->record->sentInternalTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'pending')->get(),
            ])->count();

            $failed[] = collect([
                ...$this->record->wireTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'failed')->get(),
                ...$this->record->domesticTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'failed')->get(),
                ...$this->record->sentInternalTransfers()->whereBetween('created_at', [$monthStart, $monthEnd])->where('status', 'failed')->get(),
            ])->count();
        }

        return [
            'months' => $months,
            'completed' => $completed,
            'pending' => $pending,
            'failed' => $failed,
        ];
    }

    #[Computed]
    public function loansStats()
    {
        return [
            'applied' => $this->record->loanApplications()->count(),
            'approved' => $this->record->loanApplications()->where('status', 'approved')->count(),
            'total_amount' => $this->record->loanApplications()->where('status', 'approved')->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function grantsStats()
    {
        $approvedGrants = $this->record->grantApplications()
            ->where('status', 'approved')
            ->with('grantProgram')
            ->get();

        $totalAmount = $approvedGrants->sum(fn ($grant) => $grant->grantProgram?->amount ?? 0);

        return [
            'applied' => $this->record->grantApplications()->count(),
            'approved' => $approvedGrants->count(),
            'total_amount' => $totalAmount / 100,
        ];
    }

    #[Computed]
    public function cardsStats()
    {
        return [
            'total' => $this->record->cards()->count(),
            'active' => $this->record->cards()->where('status', 'active')->count(),
            'requested' => $this->record->cards()->where('status', 'requested')->count(),
        ];
    }

    #[Computed]
    public function withdrawalsStats()
    {
        return [
            'completed' => $this->record->withdrawals()->where('status', 'completed')->count(),
            'pending' => $this->record->withdrawals()->where('status', 'pending')->count(),
            'total_amount' => $this->record->withdrawals()->where('status', 'completed')->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function vouchersStats()
    {
        return [
            'total' => $this->record->vouchers()->count(),
            'used' => $this->record->vouchers()->where('is_used', true)->count(),
            'total_amount' => $this->record->vouchers()->where('is_used', true)->sum('amount') / 100,
        ];
    }

    #[Computed]
    public function rewardsStats()
    {
        return [
            'total' => $this->record->rewards()->count(),
            'earned' => $this->record->rewards()->where('status', 'earned')->count(),
            'total_amount' => $this->record->rewards()->where('status', 'earned')->sum('points'),
        ];
    }

    #[Computed]
    public function beneficiariesStats()
    {
        return [
            'total' => $this->record->beneficiaries()->count(),
            'verified' => $this->record->beneficiaries()->where('is_verified', true)->count(),
        ];
    }

    #[Computed]
    public function taxRefundsStats()
    {
        return [
            'total' => $this->record->taxRefunds()->count(),
            'pending' => $this->record->taxRefunds()->where('status', 'pending')->count(),
            'approved' => $this->record->taxRefunds()->where('status', 'approved')->count(),
            'completed' => $this->record->taxRefunds()->where('status', 'completed')->count(),
            'rejected' => $this->record->taxRefunds()->where('status', 'rejected')->count(),
            'total_amount' => $this->record->taxRefunds()->where('status', 'completed')->sum('refund_amount'),
            'pending_amount' => $this->record->taxRefunds()->where('status', 'pending')->sum('refund_amount'),
        ];
    }

    #[Computed]
    public function moneyRequestsStats()
    {
        return [
            'total' => $this->record->moneyRequestsSent()->count() + $this->record->moneyRequestsReceived()->count(),
            'sent' => $this->record->moneyRequestsSent()->count(),
            'received' => $this->record->moneyRequestsReceived()->count(),
            'pending' => $this->record->moneyRequestsSent()->where('status', 'pending')->count(),
            'total_amount' => ($this->record->moneyRequestsSent()->where('status', 'completed')->sum('amount') + $this->record->moneyRequestsReceived()->where('status', 'completed')->sum('amount')) / 100,
        ];
    }

    #[Computed]
    public function exchangeMoneyStats()
    {
        return [
            'total' => $this->record->exchangeMoney()->count(),
            'completed' => $this->record->exchangeMoney()->where('status', 'completed')->count(),
            'pending' => $this->record->exchangeMoney()->where('status', 'pending')->count(),
            'total_from_amount' => $this->record->exchangeMoney()->where('status', 'completed')->sum('from_amount') / 100,
            'total_to_amount' => $this->record->exchangeMoney()->where('status', 'completed')->sum('to_amount') / 100,
        ];
    }

    public function updateUserInformation($data): void
    {
        // Handle date_of_birth - ensure it's a string or null
        $dateOfBirth = $data['date_of_birth'] ?? null;
        if (is_array($dateOfBirth)) {
            $dateOfBirth = $dateOfBirth['value'] ?? ($dateOfBirth[0] ?? null);
        }
        $dateOfBirth = ! empty($dateOfBirth) ? $dateOfBirth : null;

        // Handle boolean values that come as true/false from Alpine.js
        $isActive = $data['is_active'] ?? $this->record->is_active;
        $emailVerified = $data['email_verified'] ?? ($this->record->email_verified_at ? true : false);

        // Convert to boolean if they're strings or other types
        if (is_string($isActive)) {
            $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
        }
        if (is_string($emailVerified)) {
            $emailVerified = filter_var($emailVerified, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle email_verified_at based on toggle
        $emailVerifiedAt = $emailVerified
            ? ($this->record->email_verified_at ?? now())
            : null;

        // Handle kyc_level
        $kycLevel = isset($data['kyc_level']) ? (int) $data['kyc_level'] : $this->record->kyc_level;

        $this->record->update([
            'first_name' => $data['first_name'] ?? $this->record->first_name,
            'last_name' => $data['last_name'] ?? $this->record->last_name,
            'email' => $data['email'] ?? $this->record->email,
            'phone_number' => $data['phone_number'] ?? null,
            'date_of_birth' => $dateOfBirth,
            'address_line_1' => $data['address_line_1'] ?? null,
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $isActive,
            'is_verified' => $emailVerified,
            'email_verified_at' => $emailVerifiedAt,
            'kyc_level' => $kycLevel,
        ]);

        // Log the activity
        ActivityLogger::logAdmin(
            'user_information_updated',
            $this->record,
            auth()->user(),
            [
                'updated_fields' => array_keys($data),
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
            ]
        );

        Notification::make()
            ->title('User Information Updated')
            ->body('User information has been updated successfully.')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'edit-user-info');
        $this->dispatch('refresh');
    }

    /**
     * Reset user password
     */
    public function resetPassword(string $password, string $password_confirmation): void
    {
        if ($password !== $password_confirmation) {
            Notification::make()
                ->title('Password Mismatch')
                ->body('The password confirmation does not match.')
                ->danger()
                ->send();

            return;
        }

        if (strlen($password) < 8) {
            Notification::make()
                ->title('Password Too Short')
                ->body('Password must be at least 8 characters.')
                ->danger()
                ->send();

            return;
        }

        $this->record->update([
            'password' => bcrypt($password),
        ]);

        // Log the activity
        ActivityLogger::logAdmin(
            'user_password_reset',
            $this->record,
            auth()->user(),
            [
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
            ]
        );

        Notification::make()
            ->title('Password Reset')
            ->body("Password has been reset for {$this->record->email}")
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'reset-password');
    }

    /**
     * Force logout user by deleting all sessions
     */
    public function forceLogout(): void
    {
        // Delete all sessions for this user
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $this->record->id)
            ->delete();

        // Log the activity
        ActivityLogger::logAdmin(
            'user_force_logout',
            $this->record,
            auth()->user(),
            [
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
            ]
        );

        Notification::make()
            ->title('User Logged Out')
            ->body("All active sessions have been invalidated for {$this->record->email}")
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'force-logout');
    }

    /**
     * Lock or unlock user account
     */
    public function toggleAccountLock(): void
    {
        $newStatus = ! $this->record->is_active;

        $this->record->update([
            'is_active' => $newStatus,
        ]);

        // If locking, also force logout
        if (! $newStatus) {
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('user_id', $this->record->id)
                ->delete();
        }

        // Log the activity
        ActivityLogger::logAdmin(
            $newStatus ? 'user_account_unlocked' : 'user_account_locked',
            $this->record,
            auth()->user(),
            [
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
                'new_status' => $newStatus ? 'active' : 'locked',
            ]
        );

        Notification::make()
            ->title($newStatus ? 'Account Unlocked' : 'Account Locked')
            ->body($newStatus
                ? "Account has been unlocked for {$this->record->email}"
                : "Account has been locked for {$this->record->email}. User has been logged out.")
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'lock-account');
        $this->dispatch('refresh');
    }

    /**
     * Reset two-factor authentication
     */
    public function resetTwoFactor(): void
    {
        $hadTwoFactor = ! empty($this->record->two_factor_secret);

        $this->record->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Log the activity
        ActivityLogger::logAdmin(
            'user_2fa_reset',
            $this->record,
            auth()->user(),
            [
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
                'had_2fa_enabled' => $hadTwoFactor,
            ]
        );

        Notification::make()
            ->title('2FA Reset')
            ->body("Two-factor authentication has been disabled for {$this->record->email}")
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'two-factor');
        $this->dispatch('refresh');
    }

    /**
     * Generate referral code for user
     */
    public function generateReferralCode(): void
    {
        $code = $this->record->generateReferralCode();

        // Log the activity
        ActivityLogger::logAdmin(
            'user_referral_code_generated',
            $this->record,
            auth()->user(),
            [
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
                'referral_code' => $code,
            ]
        );

        Notification::make()
            ->title('Referral Code Generated')
            ->body("Referral code '{$code}' has been generated for {$this->record->full_name}")
            ->success()
            ->send();

        $this->dispatch('refresh');
    }

    // Tab and Label Methods
    public function getTabLabels(): array
    {
        return [
            'statistics' => 'Statistics',
            'information' => 'Information',
            'transactions' => 'Transactions',
            'referrals' => 'Referrals',
            'activity' => 'Activity Log',
            'security' => 'Security',
        ];
    }

    public function getStatCardLabels(): array
    {
        return [
            'send_money' => 'Send Money',
            'wire_transfers' => 'Wire Transfers',
            'domestic_transfers' => 'Domestic Transfers',
            'user_to_user' => 'User to User',
            'total_deposits' => 'Total Deposits',
            'total_balance' => 'Total Balance',
            'total_transactions' => 'Total Transactions',
            'pending_tickets' => 'Pending Tickets',
            'loans' => 'Loans',
        ];
    }

    public function getStatDescriptions(): array
    {
        return [
            'completed' => 'completed',
            'total' => 'total',
            'approved' => 'approved',
            'accounts' => 'accounts',
            'support_tickets' => 'Support tickets',
            'all_time' => 'All time',
        ];
    }
}

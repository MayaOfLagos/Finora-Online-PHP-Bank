<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use App\Services\ActivityLogger;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

class UserDetailsTabs extends Widget
{
    protected string $view = 'filament.resources.users.widgets.user-details-tabs';

    public User $record;

    protected int|string|array $columnSpan = 'full';

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
        $this->record->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address_line_1' => $data['address_line_1'] ?? null,
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? $this->record->is_active,
            'is_verified' => $data['is_verified'] ?? $this->record->is_verified,
            'kyc_level' => $data['kyc_level'] ?? $this->record->kyc_level,
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
}

<?php

namespace App\Filament\Widgets;

use App\Enums\CardStatus;
use App\Enums\DepositStatus;
use App\Enums\LoanStatus;
use App\Enums\TicketStatus;
use App\Enums\TransferStatus;
use App\Models\BankAccount;
use App\Models\Card;
use App\Models\CheckDeposit;
use App\Models\LoanApplication;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WireTransfer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Bank Accounts', BankAccount::where('is_active', true)->count())
                ->description('Active accounts')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Transfers', WireTransfer::where('status', TransferStatus::Pending)->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Pending Deposits', CheckDeposit::where('status', DepositStatus::Pending)->count())
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),

            Stat::make('Active Cards', Card::where('status', CardStatus::Active)->count())
                ->description('Issued cards')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Loan Applications', LoanApplication::where('status', LoanStatus::Pending)->count())
                ->description('Pending review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Open Tickets', SupportTicket::where('status', TicketStatus::Open)->count())
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('danger'),

            Stat::make('Total Balance', '$'.number_format(BankAccount::sum('balance') / 100, 2))
                ->description('All accounts')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}

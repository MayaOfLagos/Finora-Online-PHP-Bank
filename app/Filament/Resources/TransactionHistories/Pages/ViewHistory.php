<?php

namespace App\Filament\Resources\TransactionHistories\Pages;

use App\Filament\Resources\TransactionHistories\Schemas\TransactionHistoryTable;
use App\Filament\Resources\TransactionHistories\TransactionHistoryResource;
use App\Models\TransactionHistory;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ViewHistory extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = TransactionHistoryResource::class;

    protected string $view = 'filament.resources.transaction-histories.pages.view-history';

    public function getTitle(): string
    {
        return 'Transaction History';
    }

    public function form($form)
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->disabled()
                    ->dehydrated(),
                \Filament\Forms\Components\TextInput::make('reference_number')
                    ->label('Reference Number')
                    ->disabled()
                    ->dehydrated(),
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->required(),
                \Filament\Forms\Components\Select::make('transaction_type')
                    ->label('Transaction Type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'transfer' => 'Transfer',
                        'refund' => 'Refund',
                        'adjustment' => 'Adjustment',
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ])
                    ->required(),
                \Filament\Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                \Filament\Forms\Components\TextInput::make('currency')
                    ->label('Currency')
                    ->required()
                    ->maxLength(3),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'reversed' => 'Reversed',
                    ])
                    ->required(),
                \Filament\Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),
                \Filament\Forms\Components\Toggle::make('email_sent')
                    ->label('Email Sent to User'),
                \Filament\Forms\Components\Toggle::make('wallet_debited')
                    ->label('User Wallet Debited'),
                \Filament\Forms\Components\DateTimePicker::make('processed_at')
                    ->label('Processed At'),
                \Filament\Forms\Components\DateTimePicker::make('email_sent_at')
                    ->label('Email Sent At'),
            ])
            ->columns(2);
    }

    public function table($table)
    {
        return TransactionHistoryTable::configure($table)
            ->query(TransactionHistory::query());
    }
}

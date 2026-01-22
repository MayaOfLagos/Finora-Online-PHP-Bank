<?php

namespace App\Filament\Resources\TransactionHistories\Pages;

use App\Filament\Resources\TransactionHistories\Schemas\TransactionHistoryTable;
use App\Filament\Resources\TransactionHistories\TransactionHistoryResource;
use App\Mail\FundsAdjustedMail;
use App\Mail\PushNotificationMail;
use App\Models\BankAccount;
use App\Models\TransactionHistory;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GenerateTransaction extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = TransactionHistoryResource::class;

    protected string $view = 'filament.resources.transaction-histories.pages.generate-transaction';

    public function getTitle(): string
    {
        return 'Generate Transaction';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Generate Transaction')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('reference_number')
                        ->label('Reference Number')
                        ->default(fn (): string => strtoupper(Str::random(15)))
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Select::make('user_id')
                        ->relationship('user')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->searchable(['first_name', 'last_name', 'email'])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $user = \App\Models\User::find($state);
                                if ($user && $user->bankAccounts->count() > 0) {
                                    $set('bank_account_id', $user->bankAccounts->first()->id);
                                }
                            }
                        }),
                    Select::make('bank_account_id')
                        ->label('Bank Account')
                        ->options(function (callable $get) {
                            $userId = $get('user_id');
                            if (!$userId) {
                                return [];
                            }
                            $user = \App\Models\User::find($userId);
                            if (!$user) {
                                return [];
                            }
                            return $user->bankAccounts->mapWithKeys(function ($account) {
                                return [
                                    $account->id => $account->accountType->name . ' - ' . $account->account_number . ' (' . $account->currency . ')'
                                ];
                            });
                        })
                        ->required()
                        ->searchable()
                        ->helperText('Select the account to debit/credit'),
                    Select::make('transaction_type')
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
                        ->required()
                        ->live(),
                    TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0.01)
                        ->step(0.01)
                        ->helperText(function (callable $get) {
                            $accountId = $get('bank_account_id');
                            $transactionType = $get('transaction_type');

                            if (!$accountId) {
                                return 'Please select a bank account first';
                            }

                            $account = BankAccount::find($accountId);
                            if (!$account) {
                                return '';
                            }

                            $currentBalance = $account->balance / 100;
                            $amount = (float) ($get('amount') ?? 0);

                            if (in_array($transactionType, ['debit', 'withdrawal'])) {
                                $newBalance = $currentBalance - $amount;
                                if ($newBalance < 0) {
                                    return "⚠️ Insufficient funds! Current balance: $" . number_format($currentBalance, 2);
                                }
                                return "New balance will be: $" . number_format($newBalance, 2);
                            } elseif (in_array($transactionType, ['credit', 'deposit', 'refund'])) {
                                $newBalance = $currentBalance + $amount;
                                return "New balance will be: $" . number_format($newBalance, 2);
                            }

                            return "Current balance: $" . number_format($currentBalance, 2);
                        })
                        ->live(debounce: 500),
                    TextInput::make('currency')
                        ->label('Currency')
                        ->default('USD')
                        ->required()
                        ->maxLength(3),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                            'reversed' => 'Reversed',
                        ])
                        ->default('completed')
                        ->required(),
                    Textarea::make('description')
                        ->label('Description')
                        ->columnSpanFull()
                        ->rows(rows: 3),
                    \Filament\Schemas\Components\Section::make('Account & Notification Settings')
                        ->schema([
                            Toggle::make('update_account_balance')
                                ->label('Update Account Balance')
                                ->helperText('Automatically credit or debit the user\'s account based on transaction type')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('send_email_notification')
                                ->label('Send Email Notification')
                                ->helperText('User will receive an email about this transaction')
                                ->default(true)
                                ->inline(false),
                            Toggle::make('send_push_notification')
                                ->label('Send Push Notification')
                                ->helperText('User will receive a push notification about this transaction')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(3)
                        ->collapsible()
                        ->collapsed(false),
                ])
                ->action(function (array $data): void {
                    $user = \App\Models\User::find($data['user_id']);
                    $account = BankAccount::find($data['bank_account_id']);

                    if (!$user || !$account) {
                        Notification::make()
                            ->title('Error')
                            ->body('User or account not found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $amountInCents = (int) ($data['amount'] * 100);
                    $previousBalance = $account->balance;

                    // Determine if this is a credit or debit transaction
                    $isCredit = in_array($data['transaction_type'], ['credit', 'deposit', 'refund']);
                    $isDebit = in_array($data['transaction_type'], ['debit', 'withdrawal']);

                    // Update account balance if enabled
                    if ($data['update_account_balance'] ?? true) {
                        if ($isDebit) {
                            // Validate sufficient balance
                            if ($account->balance < $amountInCents) {
                                Notification::make()
                                    ->title('Insufficient Balance')
                                    ->body("Cannot debit \${$data['amount']}. Current balance is \$" . number_format($previousBalance / 100, 2))
                                    ->danger()
                                    ->duration(5000)
                                    ->send();
                                return;
                            }
                            $account->decrement('balance', $amountInCents);
                        } elseif ($isCredit) {
                            $account->increment('balance', $amountInCents);
                        }

                        $account->refresh();
                    }

                    // Create transaction record
                    $transaction = TransactionHistory::create([
                        'user_id' => $data['user_id'],
                        'reference_number' => $data['reference_number'],
                        'transaction_type' => $data['transaction_type'],
                        'amount' => $data['amount'],
                        'currency' => $data['currency'],
                        'status' => $data['status'],
                        'description' => $data['description'] ?? null,
                        'generated_by' => auth()->id(),
                        'uuid' => (string) Str::uuid(),
                    ]);

                    // Send notifications
                    $notificationsSent = [];

                    if ($data['send_email_notification'] ?? true) {
                        try {
                            Mail::to($user->email)->send(
                                new FundsAdjustedMail(
                                    user: $user,
                                    account: $account,
                                    action: $isCredit ? 'add' : 'deduct',
                                    amount: $data['amount'],
                                    reason: $data['description'] ?? 'Transaction generated by administrator',
                                    previousBalance: $previousBalance / 100,
                                    newBalance: $account->balance / 100,
                                )
                            );
                            $notificationsSent[] = 'Email';
                        } catch (\Exception $e) {
                            \Log::error('Failed to send transaction email: ' . $e->getMessage());
                        }
                    }

                    if ($data['send_push_notification'] ?? true) {
                        try {
                            $transactionType = ucfirst($data['transaction_type']);
                            Mail::to($user->email)->send(
                                new PushNotificationMail(
                                    user: $user,
                                    title: "{$transactionType} Transaction - Finora Bank",
                                    message: "Your {$account->accountType->name} account has been " . ($isCredit ? 'credited' : 'debited') . " with \${$data['amount']} {$account->currency}. New balance: \$" . number_format($account->balance / 100, 2) . " {$account->currency}. Ref: {$data['reference_number']}",
                                )
                            );
                            $notificationsSent[] = 'Push';
                        } catch (\Exception $e) {
                            \Log::error('Failed to send transaction push notification: ' . $e->getMessage());
                        }
                    }

                    // Log the activity
                    ActivityLogger::logAdmin(
                        'transaction_generated',
                        $user,
                        auth()->user(),
                        [
                            'transaction_id' => $transaction->id,
                            'reference_number' => $data['reference_number'],
                            'transaction_type' => $data['transaction_type'],
                            'amount' => $data['amount'],
                            'currency' => $data['currency'],
                            'account_id' => $account->id,
                            'account_number' => $account->account_number,
                            'account_balance_updated' => $data['update_account_balance'] ?? true,
                            'previous_balance' => $previousBalance / 100,
                            'new_balance' => $account->balance / 100,
                            'email_sent' => in_array('Email', $notificationsSent),
                            'push_sent' => in_array('Push', $notificationsSent),
                            'notifications_sent' => $notificationsSent,
                        ]
                    );

                    $emoji = $isCredit ? '✅' : '⚠️';
                    $notificationMessage = "**Transaction:** {$data['transaction_type']}\n**Amount:** \${$data['amount']} {$data['currency']}\n**Status:** {$data['status']}";

                    if ($data['update_account_balance'] ?? true) {
                        $notificationMessage .= "\n**New Balance:** \$" . number_format($account->balance / 100, 2);
                    }

                    if (!empty($notificationsSent)) {
                        $notificationMessage .= "\n📧 **Notifications Sent:** " . implode(', ', $notificationsSent);
                    } else {
                        $notificationMessage .= "\n⚠️ **No notifications sent** (disabled by admin)";
                    }

                    Notification::make()
                        ->title($emoji . ' Transaction Generated Successfully')
                        ->body($notificationMessage)
                        ->success()
                        ->duration(6000)
                        ->send();

                    $this->dispatch('transaction-generated');
                })
                ->modalHeading('Generate New Transaction')
                ->modalSubmitActionLabel('Generate')
                ->modalWidth('2xl'),
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return TransactionHistoryTable::configure($table)
            ->query(TransactionHistory::query());
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\AdminNotificationMail;
use App\Mail\BankAccountCreatedMail;
use App\Mail\FundsAdjustedMail;
use App\Mail\PushNotificationMail;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Services\ActivityLogger;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Mail;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.view-user';

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function togglePermission(string $permission): void
    {
        $currentValue = $this->record->{$permission};
        $this->record->update([$permission => ! $currentValue]);

        // Log the activity
        ActivityLogger::logAdmin(
            'user_permission_changed',
            $this->record,
            auth()->user(),
            [
                'permission' => $permission,
                'old_value' => $currentValue,
                'new_value' => ! $currentValue,
                'target_user_id' => $this->record->id,
                'target_user_email' => $this->record->email,
            ]
        );

        Notification::make()
            ->title('Permission Updated')
            ->body(ucfirst(str_replace('_', ' ', $permission)).' has been '.(! $currentValue ? 'enabled' : 'disabled').'.')
            ->success()
            ->send();

        // Refresh the page to show updated state
        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notify_user')
                ->label('Notify User')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->modalWidth(Width::Large)
                ->form([
                    Select::make('type')
                        ->label('Notification Type')
                        ->options([
                            'email' => 'Email',
                            'sms' => 'SMS',
                            'push' => 'Push Notification',
                            'all' => 'All Channels',
                        ])
                        ->default('email')
                        ->required(),

                    TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->maxLength(255),

                    MarkdownEditor::make('message')
                        ->label('Message')
                        ->required()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'link',
                            'bulletList',
                            'orderedList',
                            'heading',
                        ])
                        ->disableToolbarButtons([
                            'attachFiles',
                            'codeBlock',
                        ]),

                    DateTimePicker::make('schedule_at')
                        ->label('Schedule For')
                        ->helperText('Leave empty to send immediately'),
                ])
                ->action(function (array $data) {
                    // Send notification email
                    Mail::to($this->record->email)->send(
                        new AdminNotificationMail(
                            user: $this->record,
                            subject: $data['subject'],
                            message: $data['message'],
                        )
                    );

                    // Log the activity
                    ActivityLogger::logAdmin(
                        'user_notified',
                        $this->record,
                        auth()->user(),
                        [
                            'type' => $data['type'],
                            'subject' => $data['subject'],
                            'scheduled_at' => $data['schedule_at'] ?? null,
                        ]
                    );

                    Notification::make()
                        ->title('Notification Sent')
                        ->body("Email notification sent to {$this->record->email}")
                        ->success()
                        ->send();
                }),

            Action::make('manage_funds')
                ->label('Manage Funds')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->modalWidth(Width::Large)
                ->modalHeading('Modify User Balance')
                ->form([
                    Select::make('account_id')
                        ->label('Select Bank Account')
                        ->options(function () {
                            return $this->record->bankAccounts->mapWithKeys(function ($account) {
                                return [
                                    $account->id => $account->accountType->name . ' - ' . $account->account_number . ' (' . $account->currency . ')'
                                ];
                            });
                        })
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $account = BankAccount::find($state);
                                $set('current_balance', $account ? number_format($account->balance / 100, 2) : '0.00');
                                $set('currency', $account?->currency ?? 'USD');
                            }
                        })
                        ->helperText('Choose the account to modify'),

                    \Filament\Forms\Components\Placeholder::make('current_balance_display')
                        ->label('Current Balance')
                        ->content(function (callable $get) {
                            $accountId = $get('account_id');
                            if ($accountId) {
                                $account = BankAccount::find($accountId);
                                if ($account) {
                                    return '$' . number_format($account->balance / 100, 2) . ' ' . $account->currency;
                                }
                            }
                            return 'Select an account first';
                        }),

                    \Filament\Forms\Components\Radio::make('action')
                        ->label('Transaction Type')
                        ->options([
                            'add' => 'Add Balance (Credit)',
                            'deduct' => 'Subtract Balance (Debit)',
                        ])
                        ->default('add')
                        ->required()
                        ->inline()
                        ->live(),

                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->suffix(fn (callable $get) => $get('currency') ?? 'USD')
                        ->helperText(function (callable $get) {
                            $action = $get('action');
                            $accountId = $get('account_id');

                            if (!$accountId) {
                                return 'Please select an account first';
                            }

                            $account = BankAccount::find($accountId);
                            if (!$account) {
                                return '';
                            }

                            $currentBalance = $account->balance / 100;
                            $amount = (float) ($get('amount') ?? 0);

                            if ($action === 'add') {
                                $newBalance = $currentBalance + $amount;
                                return "New balance will be: $" . number_format($newBalance, 2);
                            } else {
                                $newBalance = $currentBalance - $amount;
                                if ($newBalance < 0) {
                                    return "⚠️ Insufficient funds! Current balance: $" . number_format($currentBalance, 2);
                                }
                                return "New balance will be: $" . number_format($newBalance, 2);
                            }
                        })
                        ->live(debounce: 500),

                    MarkdownEditor::make('reason')
                        ->label('Note (Optional)')
                        ->placeholder('Enter a reason for this transaction...')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'bulletList',
                            'orderedList',
                        ])
                        ->disableToolbarButtons([
                            'attachFiles',
                            'codeBlock',
                            'link',
                            'heading',
                        ])
                        ->helperText('Provide details about this balance modification'),

                    \Filament\Schemas\Components\Section::make('Notification Settings')
                        ->schema([
                            Toggle::make('send_email_notification')
                                ->label('Send Email Notification')
                                ->helperText('User will receive an email about this balance change')
                                ->default(true)
                                ->inline(false),

                            Toggle::make('send_push_notification')
                                ->label('Send Push Notification')
                                ->helperText('User will receive a push notification about this balance change')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->collapsed(false),

                    \Filament\Forms\Components\Hidden::make('currency'),
                    \Filament\Forms\Components\Hidden::make('current_balance'),
                ])
                ->action(function (array $data) {
                    $account = BankAccount::find($data['account_id']);

                    if (!$account) {
                        Notification::make()
                            ->title('Account Not Found')
                            ->body('The selected bank account could not be found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $amountInCents = (int) ($data['amount'] * 100);
                    $previousBalance = $account->balance;

                    // Validate transaction
                    if ($data['action'] === 'deduct') {
                        if ($account->balance < $amountInCents) {
                            Notification::make()
                                ->title('Insufficient Balance')
                                ->body("Cannot deduct \${$data['amount']}. Current balance is \$" . number_format($previousBalance / 100, 2))
                                ->danger()
                                ->duration(5000)
                                ->send();
                            return;
                        }
                    }

                    // Perform transaction
                    if ($data['action'] === 'add') {
                        $account->increment('balance', $amountInCents);
                        $transactionType = 'credit';
                        $transactionLabel = 'Credit';
                        $emoji = '✅';
                    } else {
                        $account->decrement('balance', $amountInCents);
                        $transactionType = 'debit';
                        $transactionLabel = 'Debit';
                        $emoji = '⚠️';
                    }

                    $account->refresh();

                    // Create transaction history record
                    $transaction = TransactionHistory::create([
                        'user_id' => $this->record->id,
                        'reference_number' => strtoupper(\Illuminate\Support\Str::random(15)),
                        'transaction_type' => $transactionType,
                        'amount' => $data['amount'],
                        'currency' => $account->currency,
                        'status' => 'completed',
                        'description' => $data['reason'] ?? 'Balance adjustment by administrator',
                        'generated_by' => auth()->id(),
                        'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    ]);

                    // Send notifications based on toggle settings
                    $notificationsSent = [];

                    if ($data['send_email_notification'] ?? true) {
                        try {
                            Mail::to($this->record->email)->send(
                                new FundsAdjustedMail(
                                    user: $this->record,
                                    account: $account,
                                    action: $data['action'],
                                    amount: $data['amount'],
                                    reason: $data['reason'] ?? 'Balance adjustment by administrator',
                                    previousBalance: $previousBalance / 100,
                                    newBalance: $account->balance / 100,
                                )
                            );
                            $notificationsSent[] = 'Email';
                        } catch (\Exception $e) {
                            \Log::error('Failed to send balance adjustment email: ' . $e->getMessage());
                        }
                    }

                    if ($data['send_push_notification'] ?? true) {
                        try {
                            Mail::to($this->record->email)->send(
                                new PushNotificationMail(
                                    user: $this->record,
                                    title: "{$transactionLabel} - Balance Updated",
                                    message: "Your {$account->accountType->name} account has been {$transactionLabel}ed with \${$data['amount']} {$account->currency}. New balance: \$" . number_format($account->balance / 100, 2) . " {$account->currency}",
                                )
                            );
                            $notificationsSent[] = 'Push';
                        } catch (\Exception $e) {
                            \Log::error('Failed to send balance adjustment push notification: ' . $e->getMessage());
                        }
                    }

                    // Log the activity
                    ActivityLogger::logAdmin(
                        'funds_adjusted',
                        $this->record,
                        auth()->user(),
                        [
                            'transaction_id' => $transaction->id,
                            'reference_number' => $transaction->reference_number,
                            'action' => $data['action'],
                            'transaction_type' => $transactionType,
                            'account_id' => $account->id,
                            'account_number' => $account->account_number,
                            'amount' => $data['amount'],
                            'currency' => $account->currency,
                            'reason' => $data['reason'] ?? 'Balance adjustment by administrator',
                            'previous_balance' => $previousBalance / 100,
                            'new_balance' => $account->balance / 100,
                            'email_sent' => in_array('Email', $notificationsSent),
                            'push_sent' => in_array('Push', $notificationsSent),
                            'notifications_sent' => $notificationsSent,
                        ]
                    );

                    $notificationMessage = "**{$transactionLabel}:** \${$data['amount']} {$account->currency}\n**New Balance:** \$" . number_format($account->balance / 100, 2);

                    if (!empty($notificationsSent)) {
                        $notificationMessage .= "\n📧 **Notifications Sent:** " . implode(', ', $notificationsSent);
                    } else {
                        $notificationMessage .= "\n⚠️ **No notifications sent** (disabled by admin)";
                    }

                    Notification::make()
                        ->title($emoji . ' Balance Updated Successfully')
                        ->body($notificationMessage)
                        ->success()
                        ->duration(6000)
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('create_bank_account')
                ->label('Create Bank Account')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalWidth(Width::Large)
                ->form([
                    Select::make('account_type_id')
                        ->label('Account Type')
                        ->options(fn () => AccountType::pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    TextInput::make('account_number')
                        ->label('Account Number')
                        ->required()
                        ->unique('bank_accounts', 'account_number')
                        ->maxLength(20),

                    TextInput::make('routing_number')
                        ->label('Routing Number')
                        ->maxLength(9),

                    TextInput::make('swift_code')
                        ->label('SWIFT Code')
                        ->maxLength(11),

                    Select::make('currency')
                        ->label('Currency')
                        ->options([
                            'USD' => 'US Dollar (USD)',
                            'EUR' => 'Euro (EUR)',
                            'GBP' => 'British Pound (GBP)',
                            'JPY' => 'Japanese Yen (JPY)',
                            'CAD' => 'Canadian Dollar (CAD)',
                            'AUD' => 'Australian Dollar (AUD)',
                            'CHF' => 'Swiss Franc (CHF)',
                            'CNY' => 'Chinese Yuan (CNY)',
                            'INR' => 'Indian Rupee (INR)',
                            'NGN' => 'Nigerian Naira (NGN)',
                        ])
                        ->default('USD')
                        ->required(),

                    Select::make('is_primary')
                        ->label('Set as Primary Account')
                        ->boolean()
                        ->default(fn () => $this->record->bankAccounts()->count() === 0)
                        ->required(),

                    TextInput::make('balance')
                        ->label('Initial Balance')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->minValue(0),
                ])
                ->action(function (array $data) {
                    // Validate account limit
                    if ($this->record->bankAccounts()->count() >= 2) {
                        Notification::make()
                            ->title('Account Limit Reached')
                            ->body('Users can only have a maximum of 2 bank accounts.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($data['is_primary']) {
                        $this->record->bankAccounts()->update(['is_primary' => false]);
                    }

                    $account = $this->record->bankAccounts()->create([
                        'account_type_id' => $data['account_type_id'],
                        'account_number' => $data['account_number'],
                        'routing_number' => $data['routing_number'] ?? null,
                        'swift_code' => $data['swift_code'] ?? null,
                        'currency' => $data['currency'],
                        'balance' => (int) ($data['balance'] * 100),
                        'is_primary' => $data['is_primary'] ?? false,
                    ]);

                    // Send email notification
                    Mail::to($this->record->email)->send(
                        new BankAccountCreatedMail(
                            user: $this->record,
                            account: $account,
                        )
                    );

                    // Log the activity
                    ActivityLogger::logAdmin(
                        'bank_account_created',
                        $this->record,
                        auth()->user(),
                        [
                            'account_id' => $account->id,
                            'account_number' => $account->account_number,
                            'account_type' => $account->accountType->name,
                        ]
                    );

                    Notification::make()
                        ->title('Bank Account Created')
                        ->body('Bank account '.$account->account_number.' has been created successfully.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('login_as_user')
                ->label('Login as User')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Login as User')
                ->modalDescription('You are about to impersonate this user. All actions will be performed as this user.')
                ->modalSubmitActionLabel('Yes, Login as User')
                ->action(function () {
                    // Store the admin's ID in session for returning later
                    session(['impersonator_id' => auth()->id()]);

                    // Log the impersonation
                    ActivityLogger::logAdmin(
                        'user_impersonated',
                        $this->record,
                        auth()->user(),
                        [
                            'impersonated_user_id' => $this->record->id,
                            'impersonated_user_email' => $this->record->email,
                        ]
                    );

                    // Login as the user
                    auth()->login($this->record);

                    Notification::make()
                        ->title('Logged in as User')
                        ->body("You are now logged in as {$this->record->name}")
                        ->success()
                        ->send();

                    // Redirect to user dashboard
                    return redirect('/');
                }),

            Actions\DeleteAction::make()
                ->modalWidth(Width::ExtraLarge)
                ->modalDescription('Are you sure you want to delete this user? This action cannot be undone and will permanently delete all associated data including bank accounts, transactions, and documents.')
                ->successNotificationTitle('User Deleted')
                ->before(function () {
                    // Log the deletion
                    ActivityLogger::logAdmin(
                        'user_deleted',
                        $this->record,
                        auth()->user(),
                        [
                            'deleted_user_id' => $this->record->id,
                            'deleted_user_email' => $this->record->email,
                            'deleted_user_name' => $this->record->name,
                        ]
                    );
                }),
        ];
    }
}

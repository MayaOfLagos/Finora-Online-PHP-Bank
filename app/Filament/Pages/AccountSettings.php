<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AccountSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Account Settings';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    protected string $view = 'filament.pages.account-settings';

    public function mount(): void
    {
        $this->form->fill([
            'max_accounts_per_user' => Setting::getValue('accounts', 'max_accounts_per_user', 2),
            'allow_multiple_currencies' => Setting::getValue('accounts', 'allow_multiple_currencies', true),
            'require_kyc_for_account_creation' => Setting::getValue('accounts', 'require_kyc_for_account_creation', false),
            'auto_generate_account_number' => Setting::getValue('accounts', 'auto_generate_account_number', true),
            'default_currency' => Setting::getValue('accounts', 'default_currency', 'USD'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Account Limits')
                    ->description('Configure account creation limits for users')
                    ->schema([
                        TextInput::make('max_accounts_per_user')
                            ->label('Maximum Accounts Per User')
                            ->helperText('Maximum number of bank accounts a user can create')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(2)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('Account Features')
                    ->description('Enable or disable account features')
                    ->schema([
                        Toggle::make('allow_multiple_currencies')
                            ->label('Allow Multiple Currencies')
                            ->helperText('Allow users to create accounts in different currencies')
                            ->default(true),

                        Toggle::make('require_kyc_for_account_creation')
                            ->label('Require KYC for Account Creation')
                            ->helperText('Users must complete KYC verification before creating accounts')
                            ->default(false),

                        Toggle::make('auto_generate_account_number')
                            ->label('Auto-Generate Account Numbers')
                            ->helperText('Automatically generate account numbers (10-digit)')
                            ->default(true),
                    ])
                    ->columns(1),

                Section::make('Default Settings')
                    ->description('Default values for new accounts')
                    ->schema([
                        Select::make('default_currency')
                            ->label('Default Currency')
                            ->helperText('Default currency for new accounts')
                            ->options([
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                                'NGN' => 'NGN - Nigerian Naira',
                                'CAD' => 'CAD - Canadian Dollar',
                                'AUD' => 'AUD - Australian Dollar',
                                'KES' => 'KES - Kenyan Shilling',
                                'ZAR' => 'ZAR - South African Rand',
                                'GHS' => 'GHS - Ghanaian Cedi',
                                'INR' => 'INR - Indian Rupee',
                            ])
                            ->default('USD')
                            ->required(),
                    ])
                    ->columns(1),

                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                ])
                    ->alignment('end')
                    ->fullWidth(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            Setting::setValue('accounts', 'max_accounts_per_user', $data['max_accounts_per_user'], 'integer');
            Setting::setValue('accounts', 'allow_multiple_currencies', $data['allow_multiple_currencies'], 'boolean');
            Setting::setValue('accounts', 'require_kyc_for_account_creation', $data['require_kyc_for_account_creation'], 'boolean');
            Setting::setValue('accounts', 'auto_generate_account_number', $data['auto_generate_account_number'], 'boolean');
            Setting::setValue('accounts', 'default_currency', $data['default_currency'], 'string');

            Notification::make()
                ->success()
                ->title('Settings saved')
                ->body('Account settings have been updated successfully.')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}

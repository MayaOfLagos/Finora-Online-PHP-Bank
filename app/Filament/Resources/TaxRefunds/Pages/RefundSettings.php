<?php

namespace App\Filament\Resources\TaxRefunds\Pages;

use App\Filament\Resources\TaxRefunds\TaxRefundResource;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class RefundSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = TaxRefundResource::class;

    protected string $view = 'filament.resources.tax-refunds.pages.refund-settings';

    protected static ?string $title = 'Refund Settings';

    protected static ?string $navigationLabel = 'Refund Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'refund_enabled' => Setting::get('irs_refund', 'enabled', true),
            'min_refund_amount' => Setting::get('irs_refund', 'min_amount', 100),
            'max_refund_amount' => Setting::get('irs_refund', 'max_amount', 500000),
            'processing_days' => Setting::get('irs_refund', 'processing_days', 21),
            'require_ssn' => Setting::get('irs_refund', 'require_ssn', true),
            'require_bank_account' => Setting::get('irs_refund', 'require_bank_account', true),
            'auto_approve_below' => Setting::get('irs_refund', 'auto_approve_below', 0),
            'notification_email' => Setting::get('irs_refund', 'notification_email', ''),
            'terms_message' => Setting::get('irs_refund', 'terms_message', ''),
            'supported_filing_status' => Setting::get('irs_refund', 'supported_filing_status', ['single', 'married_filing_jointly', 'married_filing_separately', 'head_of_household']),
            'supported_currencies' => Setting::get('irs_refund', 'supported_currencies', ['USD']),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Settings')
                    ->description('Configure basic IRS refund settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Toggle::make('refund_enabled')
                            ->label('Enable IRS Refunds')
                            ->helperText('Allow users to submit IRS refund requests')
                            ->default(true),
                        Grid::make(2)->schema([
                            TextInput::make('min_refund_amount')
                                ->label('Minimum Refund Amount')
                                ->numeric()
                                ->prefix('$')
                                ->default(100)
                                ->required(),
                            TextInput::make('max_refund_amount')
                                ->label('Maximum Refund Amount')
                                ->numeric()
                                ->prefix('$')
                                ->default(500000)
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('processing_days')
                                ->label('Processing Days')
                                ->numeric()
                                ->suffix('days')
                                ->default(21)
                                ->helperText('Expected processing time')
                                ->required(),
                            TextInput::make('auto_approve_below')
                                ->label('Auto-Approve Below')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->helperText('Set to 0 to disable auto-approval'),
                        ]),
                    ]),
                Section::make('Verification Requirements')
                    ->description('Configure required verification for refunds')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('require_ssn')
                                ->label('Require SSN/TIN')
                                ->helperText('Require Social Security Number or Tax ID')
                                ->default(true),
                            Toggle::make('require_bank_account')
                                ->label('Require Bank Account')
                                ->helperText('User must have a verified bank account')
                                ->default(true),
                        ]),
                        Select::make('supported_filing_status')
                            ->label('Supported Filing Status')
                            ->multiple()
                            ->options([
                                'single' => 'Single',
                                'married_filing_jointly' => 'Married Filing Jointly',
                                'married_filing_separately' => 'Married Filing Separately',
                                'head_of_household' => 'Head of Household',
                                'qualifying_widow' => 'Qualifying Widow(er)',
                            ])
                            ->default(['single', 'married_filing_jointly', 'married_filing_separately', 'head_of_household']),
                        Select::make('supported_currencies')
                            ->label('Supported Currencies')
                            ->multiple()
                            ->options([
                                'USD' => 'US Dollar (USD)',
                            ])
                            ->default(['USD']),
                    ]),
                Section::make('Notifications')
                    ->description('Configure notification settings')
                    ->icon('heroicon-o-bell')
                    ->schema([
                        TextInput::make('notification_email')
                            ->label('Admin Notification Email')
                            ->email()
                            ->helperText('Email to receive refund notifications'),
                        Textarea::make('terms_message')
                            ->label('Terms & Conditions Message')
                            ->rows(4)
                            ->helperText('Displayed to users during refund application'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-o-check')
                ->action(function () {
                    $data = $this->form->getState();

                    Setting::set('irs_refund', 'enabled', $data['refund_enabled'], 'boolean');
                    Setting::set('irs_refund', 'min_amount', $data['min_refund_amount'], 'integer');
                    Setting::set('irs_refund', 'max_amount', $data['max_refund_amount'], 'integer');
                    Setting::set('irs_refund', 'processing_days', $data['processing_days'], 'integer');
                    Setting::set('irs_refund', 'require_ssn', $data['require_ssn'], 'boolean');
                    Setting::set('irs_refund', 'require_bank_account', $data['require_bank_account'], 'boolean');
                    Setting::set('irs_refund', 'auto_approve_below', $data['auto_approve_below'], 'integer');
                    Setting::set('irs_refund', 'notification_email', $data['notification_email'] ?? '', 'string');
                    Setting::set('irs_refund', 'terms_message', $data['terms_message'] ?? '', 'string');
                    Setting::set('irs_refund', 'supported_filing_status', $data['supported_filing_status'], 'array');
                    Setting::set('irs_refund', 'supported_currencies', $data['supported_currencies'], 'array');

                    Notification::make()
                        ->title('Settings Saved')
                        ->body('IRS refund settings have been updated successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\TaxRefunds\Schemas;

use App\Enums\TaxRefundStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TaxRefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Taxpayer Information')
                    ->description('Personal and account details of the taxpayer')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('uuid')
                                    ->label('UUID')
                                    ->default(fn (): string => (string) Str::uuid())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->helperText('Unique identifier'),

                                TextInput::make('reference_number')
                                    ->label('Reference Number')
                                    ->default(fn (): string => 'TAXREF-'.strtoupper(Str::random(10)))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->helperText('Auto-generated reference'),

                                Select::make('user_id')
                                    ->label('Taxpayer')
                                    ->relationship('user')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                                    ->searchable(['first_name', 'last_name', 'email'])
                                    ->preload()
                                    ->required()
                                    ->helperText('Select the taxpayer'),

                                Select::make('bank_account_id')
                                    ->label('Deposit Account')
                                    ->relationship('bankAccount', 'account_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Account for refund deposit'),

                                TextInput::make('ssn_tin')
                                    ->label('SSN/TIN (Last 4 digits)')
                                    ->placeholder('XXXX')
                                    ->maxLength(4)
                                    ->mask('9999')
                                    ->helperText('Last 4 digits for verification'),
                            ]),
                    ]),

                Section::make('Tax Filing Details')
                    ->description('Information from the tax return')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tax_year')
                                    ->label('Tax Year')
                                    ->placeholder('e.g., 2025')
                                    ->required()
                                    ->maxLength(4)
                                    ->numeric()
                                    ->minValue(2000)
                                    ->maxValue(date('Y'))
                                    ->helperText('The tax year for this refund'),

                                Select::make('filing_status')
                                    ->label('Filing Status')
                                    ->options([
                                        'single' => 'Single',
                                        'married_filing_jointly' => 'Married Filing Jointly',
                                        'married_filing_separately' => 'Married Filing Separately',
                                        'head_of_household' => 'Head of Household',
                                        'qualifying_widow' => 'Qualifying Widow(er)',
                                    ])
                                    ->required()
                                    ->helperText('IRS filing status'),

                                TextInput::make('employer_name')
                                    ->label('Employer Name')
                                    ->placeholder('Company Inc.')
                                    ->maxLength(255)
                                    ->helperText('Primary employer name'),

                                TextInput::make('employer_ein')
                                    ->label('Employer EIN')
                                    ->placeholder('XX-XXXXXXX')
                                    ->maxLength(12)
                                    ->helperText('Employer Identification Number'),

                                Select::make('state')
                                    ->label('State')
                                    ->options(self::getUSStates())
                                    ->searchable()
                                    ->helperText('State of residence'),
                            ]),
                    ]),

                Section::make('Income & Withholding')
                    ->description('Financial details from W-2 and tax return')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('gross_income')
                                    ->label('Gross Income')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Total gross income reported'),

                                TextInput::make('federal_withheld')
                                    ->label('Federal Tax Withheld')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Federal income tax withheld'),

                                TextInput::make('state_withheld')
                                    ->label('State Tax Withheld')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('State income tax withheld'),

                                TextInput::make('refund_amount')
                                    ->label('Refund Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Expected refund amount'),

                                TextInput::make('currency')
                                    ->label('Currency')
                                    ->default('USD')
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(3),

                                TextInput::make('irs_reference_number')
                                    ->label('IRS Reference Number')
                                    ->placeholder('IRS confirmation number')
                                    ->maxLength(255)
                                    ->helperText('Reference from IRS (if available)'),
                            ]),
                    ]),

                Section::make('ID.me Verification')
                    ->description('Identity verification status through ID.me')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('idme_verified')
                                    ->label('ID.me Verified')
                                    ->helperText('Has the taxpayer verified their identity?')
                                    ->live(),

                                TextInput::make('idme_verification_id')
                                    ->label('ID.me Verification ID')
                                    ->placeholder('Verification reference')
                                    ->visible(fn ($get) => $get('idme_verified'))
                                    ->helperText('ID.me transaction reference'),

                                DateTimePicker::make('idme_verified_at')
                                    ->label('Verified At')
                                    ->displayFormat('M d, Y H:i')
                                    ->visible(fn ($get) => $get('idme_verified'))
                                    ->helperText('When identity was verified'),
                            ]),
                    ]),

                Section::make('Status & Processing')
                    ->description('Application status and processing dates')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options(TaxRefundStatus::class)
                                    ->default(TaxRefundStatus::Pending)
                                    ->required()
                                    ->helperText('Current processing status'),

                                DateTimePicker::make('submitted_at')
                                    ->label('Submitted At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When the request was submitted'),

                                DateTimePicker::make('processed_at')
                                    ->label('Processed At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When processing began'),

                                DateTimePicker::make('approved_at')
                                    ->label('Approved At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When the refund was approved'),

                                DateTimePicker::make('completed_at')
                                    ->label('Completed At')
                                    ->displayFormat('M d, Y H:i')
                                    ->helperText('When the refund was disbursed'),
                            ]),

                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Reason for rejection (if applicable)...')
                            ->visible(fn ($get) => $get('status') === TaxRefundStatus::Rejected->value || $get('status') === TaxRefundStatus::Rejected)
                            ->helperText('Detailed reason for rejection'),
                    ]),
            ]);
    }

    protected static function getUSStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia',
        ];
    }
}

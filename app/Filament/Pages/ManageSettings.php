<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 0;

    protected static ?int $navigationGroupSort = 999;

    protected static ?string $title = 'System Settings';

    protected static ?string $slug = 'system-settings';

    protected static ?string $navigationLabel = 'System Settings';

    protected string $view = 'filament.pages.manage-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadSettings());
    }

    protected function loadSettings(): array
    {
        return [
            // General Settings
            'site_name' => Setting::getValue('general', 'site_name', 'Finora Bank'),
            'site_tagline' => Setting::getValue('general', 'site_tagline', 'Your Trusted Banking Partner'),
            'site_description' => Setting::getValue('general', 'site_description', ''),
            'site_email' => Setting::getValue('general', 'site_email', 'info@finorabank.com'),
            'site_phone' => Setting::getValue('general', 'site_phone', '+1 (800) 555-0199'),
            'site_address' => Setting::getValue('general', 'site_address', ''),
            'site_currency' => Setting::getValue('general', 'site_currency', 'USD'),
            'site_timezone' => Setting::getValue('general', 'site_timezone', 'UTC'),

            // Branding
            'site_logo' => Setting::getValue('branding', 'site_logo', ''),
            'site_logo_dark' => Setting::getValue('branding', 'site_logo_dark', ''),
            'site_favicon' => Setting::getValue('branding', 'site_favicon', ''),
            'primary_color' => Setting::getValue('branding', 'primary_color', '#3b82f6'),
            'secondary_color' => Setting::getValue('branding', 'secondary_color', '#1e40af'),

            // SMTP Settings
            'mail_mailer' => Setting::getValue('mail', 'mailer', 'smtp'),
            'mail_host' => Setting::getValue('mail', 'host', ''),
            'mail_port' => Setting::getValue('mail', 'port', '587'),
            'mail_username' => Setting::getValue('mail', 'username', ''),
            'mail_password' => Setting::getValue('mail', 'password', ''),
            'mail_encryption' => Setting::getValue('mail', 'encryption', 'tls'),
            'mail_from_address' => Setting::getValue('mail', 'from_address', ''),
            'mail_from_name' => Setting::getValue('mail', 'from_name', ''),

            // Banking Settings
            'bank_name' => Setting::getValue('banking', 'bank_name', 'Finora Bank'),
            'bank_code' => Setting::getValue('banking', 'bank_code', ''),
            'swift_code' => Setting::getValue('banking', 'swift_code', ''),
            'routing_number' => Setting::getValue('banking', 'routing_number', ''),
            'wire_transfer_enabled' => (bool) Setting::getValue('banking', 'wire_transfer_enabled', true),
            'domestic_transfer_enabled' => (bool) Setting::getValue('banking', 'domestic_transfer_enabled', true),
            'internal_transfer_enabled' => (bool) Setting::getValue('banking', 'internal_transfer_enabled', true),

            // Transfer Limits - Wire Transfer
            'wire_daily_limit' => Setting::getValue('transfers', 'wire_daily_limit', '100000'),
            'wire_per_transaction_limit' => Setting::getValue('transfers', 'wire_per_transaction_limit', '50000'),

            // Transfer Limits - Domestic Transfer
            'domestic_daily_limit' => Setting::getValue('transfers', 'domestic_daily_limit', '50000'),
            'domestic_per_transaction_limit' => Setting::getValue('transfers', 'domestic_per_transaction_limit', '25000'),

            // Transfer Limits - Internal Transfer
            'internal_daily_limit' => Setting::getValue('transfers', 'internal_daily_limit', '100000'),
            'internal_per_transaction_limit' => Setting::getValue('transfers', 'internal_per_transaction_limit', '50000'),

            // Fee Settings - Wire Transfer
            'wire_flat_fee' => Setting::getValue('transfers', 'wire_flat_fee', '25'),
            'wire_percentage_fee' => Setting::getValue('transfers', 'wire_percentage_fee', '0.5'),

            // Fee Settings - Domestic Transfer
            'domestic_flat_fee' => Setting::getValue('transfers', 'domestic_flat_fee', '5'),
            'domestic_percentage_fee' => Setting::getValue('transfers', 'domestic_percentage_fee', '0'),

            // Fee Settings - Internal Transfer
            'internal_flat_fee' => Setting::getValue('transfers', 'internal_flat_fee', '0'),
            'internal_percentage_fee' => Setting::getValue('transfers', 'internal_percentage_fee', '0'),

            // Card Fees
            'card_issuance_fee' => Setting::getValue('fees', 'card_issuance_fee', '10'),
            'card_replacement_fee' => Setting::getValue('fees', 'card_replacement_fee', '15'),

            // Security Settings
            'two_factor_enabled' => (bool) Setting::getValue('security', 'two_factor_enabled', true),
            'email_verification_required' => (bool) Setting::getValue('security', 'email_verification_required', true),
            'kyc_required' => (bool) Setting::getValue('security', 'kyc_required', true),
            'session_lifetime' => Setting::getValue('security', 'session_lifetime', '120'),
            'max_login_attempts' => Setting::getValue('security', 'max_login_attempts', '5'),
            'lockout_duration' => Setting::getValue('security', 'lockout_duration', '30'),
            'password_min_length' => Setting::getValue('security', 'password_min_length', '8'),
            'require_special_char' => (bool) Setting::getValue('security', 'require_special_char', true),
            'require_uppercase' => (bool) Setting::getValue('security', 'require_uppercase', true),

            // Login Verification Settings
            'login_require_email_otp' => (bool) Setting::getValue('security', 'login_require_email_otp', true),
            'login_require_pin' => (bool) Setting::getValue('security', 'login_require_pin', true),

            // Email OTP Login Settings
            'email_otp_enabled' => (bool) Setting::getValue('security', 'email_otp_enabled', false),
            'email_otp_enforce_admin' => (bool) Setting::getValue('security', 'email_otp_enforce_admin', false),
            'email_otp_enforce_user' => (bool) Setting::getValue('security', 'email_otp_enforce_user', false),
            'email_otp_expiry' => Setting::getValue('security', 'email_otp_expiry', '10'),
            'email_otp_length' => Setting::getValue('security', 'email_otp_length', '6'),

            // Transfer OTP Verification Settings
            'transfer_otp_enabled' => (bool) Setting::getValue('security', 'transfer_otp_enabled', true),

            // ReCAPTCHA Settings
            'recaptcha_enabled' => (bool) Setting::getValue('security', 'recaptcha_enabled', false),
            'recaptcha_enforce_admin' => (bool) Setting::getValue('security', 'recaptcha_enforce_admin', false),
            'recaptcha_enforce_user' => (bool) Setting::getValue('security', 'recaptcha_enforce_user', false),
            'recaptcha_site_key' => Setting::getValue('security', 'recaptcha_site_key', ''),
            'recaptcha_secret_key' => Setting::getValue('security', 'recaptcha_secret_key', ''),
            'recaptcha_version' => Setting::getValue('security', 'recaptcha_version', 'v2'),
            'recaptcha_score_threshold' => Setting::getValue('security', 'recaptcha_score_threshold', '0.5'),

            // Notification Settings
            'email_notifications' => (bool) Setting::getValue('notifications', 'email_notifications', true),
            'sms_notifications' => (bool) Setting::getValue('notifications', 'sms_notifications', false),
            'push_notifications' => (bool) Setting::getValue('notifications', 'push_notifications', false),
            'transaction_alerts' => (bool) Setting::getValue('notifications', 'transaction_alerts', true),
            'login_alerts' => (bool) Setting::getValue('notifications', 'login_alerts', true),
            'marketing_emails' => (bool) Setting::getValue('notifications', 'marketing_emails', false),

            // Deposit Settings
            'check_deposit_enabled' => (bool) Setting::getValue('deposits', 'check_deposit_enabled', true),
            'mobile_deposit_enabled' => (bool) Setting::getValue('deposits', 'mobile_deposit_enabled', true),
            'crypto_deposit_enabled' => (bool) Setting::getValue('deposits', 'crypto_deposit_enabled', true),
            'check_hold_period' => Setting::getValue('deposits', 'check_hold_period', '3'),
            'check_daily_limit' => Setting::getValue('deposits', 'check_daily_limit', '5000'),
            'check_per_transaction_limit' => Setting::getValue('deposits', 'check_per_transaction_limit', '2500'),
            'mobile_daily_limit' => Setting::getValue('deposits', 'mobile_daily_limit', '10000'),
            'mobile_per_transaction_limit' => Setting::getValue('deposits', 'mobile_per_transaction_limit', '5000'),
            'crypto_daily_limit' => Setting::getValue('deposits', 'crypto_daily_limit', '10000'),
            'crypto_per_transaction_limit' => Setting::getValue('deposits', 'crypto_per_transaction_limit', '5000'),

            // Loan Settings
            'loans_enabled' => (bool) Setting::getValue('loans', 'loans_enabled', true),
            'loan_applications_enabled' => (bool) Setting::getValue('loans', 'applications_enabled', true),
            'auto_approve_loans' => (bool) Setting::getValue('loans', 'auto_approve', false),
            'require_collateral' => (bool) Setting::getValue('loans', 'require_collateral', false),
            'require_guarantor' => (bool) Setting::getValue('loans', 'require_guarantor', false),
            'require_employment_verification' => (bool) Setting::getValue('loans', 'require_employment_verification', true),
            'require_income_verification' => (bool) Setting::getValue('loans', 'require_income_verification', true),
            'min_loan_amount' => Setting::getValue('loans', 'min_loan_amount', '1000'),
            'max_loan_amount' => Setting::getValue('loans', 'max_loan_amount', '500000'),
            'default_interest_rate' => Setting::getValue('loans', 'default_interest_rate', '8.5'),
            'max_loan_term' => Setting::getValue('loans', 'max_loan_term', '360'),
            'min_loan_term' => Setting::getValue('loans', 'min_loan_term', '6'),
            'late_payment_fee' => Setting::getValue('loans', 'late_payment_fee', '35'),
            'loan_origination_fee' => Setting::getValue('loans', 'origination_fee', '1'),
            'grace_period_days' => Setting::getValue('loans', 'grace_period_days', '15'),
            'max_active_loans' => Setting::getValue('loans', 'max_active_loans', '3'),
            'min_credit_score' => Setting::getValue('loans', 'min_credit_score', '650'),

            // Grant Settings
            'grants_enabled' => (bool) Setting::getValue('grants', 'grants_enabled', true),
            'grant_applications_enabled' => (bool) Setting::getValue('grants', 'applications_enabled', true),
            'auto_approve_grants' => (bool) Setting::getValue('grants', 'auto_approve', false),
            'require_grant_documents' => (bool) Setting::getValue('grants', 'require_documents', true),
            'require_eligibility_check' => (bool) Setting::getValue('grants', 'require_eligibility_check', true),
            'max_grant_applications_per_user' => Setting::getValue('grants', 'max_applications_per_user', '5'),
            'grant_application_cooldown' => Setting::getValue('grants', 'application_cooldown_days', '30'),
            'notify_on_grant_status' => (bool) Setting::getValue('grants', 'notify_on_status_change', true),
            'allow_grant_reapplication' => (bool) Setting::getValue('grants', 'allow_reapplication', true),

            // Card Settings
            'virtual_cards_enabled' => (bool) Setting::getValue('cards', 'virtual_cards_enabled', true),
            'physical_cards_enabled' => (bool) Setting::getValue('cards', 'physical_cards_enabled', true),
            'card_daily_limit' => Setting::getValue('cards', 'card_daily_limit', '5000'),
            'card_monthly_limit' => Setting::getValue('cards', 'card_monthly_limit', '50000'),

            // Maintenance
            'maintenance_mode' => (bool) Setting::getValue('general', 'maintenance_mode', false),
            'maintenance_message' => Setting::getValue('general', 'maintenance_message', 'We are currently performing maintenance. Please check back later.'),
            'maintenance_allowed_ips' => Setting::getValue('general', 'maintenance_allowed_ips', ''),
            'maintenance_secret' => Setting::getValue('general', 'maintenance_secret', ''),

            // SEO Settings
            'seo_meta_title' => Setting::getValue('seo', 'meta_title', ''),
            'seo_meta_description' => Setting::getValue('seo', 'meta_description', ''),
            'seo_meta_keywords' => Setting::getValue('seo', 'meta_keywords', ''),
            'seo_og_title' => Setting::getValue('seo', 'og_title', ''),
            'seo_og_description' => Setting::getValue('seo', 'og_description', ''),
            'seo_og_image' => Setting::getValue('seo', 'og_image', ''),
            'seo_twitter_card' => Setting::getValue('seo', 'twitter_card', 'summary_large_image'),
            'seo_twitter_site' => Setting::getValue('seo', 'twitter_site', ''),
            'seo_google_analytics' => Setting::getValue('seo', 'google_analytics', ''),
            'seo_google_tag_manager' => Setting::getValue('seo', 'google_tag_manager', ''),
            'seo_facebook_pixel' => Setting::getValue('seo', 'facebook_pixel', ''),
            'seo_robots_txt' => Setting::getValue('seo', 'robots_txt', "User-agent: *\nAllow: /"),
            'seo_custom_head_code' => Setting::getValue('seo', 'custom_head_code', ''),

            // Feature Permissions - User Dashboard Access Controls
            // Account Features
            'perm_bank_accounts' => (bool) Setting::getValue('permissions', 'bank_accounts', true),
            'perm_beneficiaries' => (bool) Setting::getValue('permissions', 'beneficiaries', true),

            // Transfer Features
            'perm_wire_transfers' => (bool) Setting::getValue('permissions', 'wire_transfers', true),
            'perm_internal_transfers' => (bool) Setting::getValue('permissions', 'internal_transfers', true),
            'perm_domestic_transfers' => (bool) Setting::getValue('permissions', 'domestic_transfers', true),

            // Deposit Features
            'perm_check_deposits' => (bool) Setting::getValue('permissions', 'check_deposits', true),
            'perm_mobile_deposits' => (bool) Setting::getValue('permissions', 'mobile_deposits', true),
            'perm_crypto_deposits' => (bool) Setting::getValue('permissions', 'crypto_deposits', true),

            // Loans & Grants Features
            'perm_loans' => (bool) Setting::getValue('permissions', 'loans', true),
            'perm_loan_applications' => (bool) Setting::getValue('permissions', 'loan_applications', true),
            'perm_grants' => (bool) Setting::getValue('permissions', 'grants', true),
            'perm_grant_applications' => (bool) Setting::getValue('permissions', 'grant_applications', true),
            'perm_tax_refunds' => (bool) Setting::getValue('permissions', 'tax_refunds', true),

            // Card Features
            'perm_cards' => (bool) Setting::getValue('permissions', 'cards', true),
            'perm_virtual_cards' => (bool) Setting::getValue('permissions', 'virtual_cards', true),
            'perm_physical_cards' => (bool) Setting::getValue('permissions', 'physical_cards', true),

            // Other Financial Features
            'perm_withdrawals' => (bool) Setting::getValue('permissions', 'withdrawals', true),
            'perm_money_requests' => (bool) Setting::getValue('permissions', 'money_requests', true),
            'perm_exchange_money' => (bool) Setting::getValue('permissions', 'exchange_money', true),
            'perm_vouchers' => (bool) Setting::getValue('permissions', 'vouchers', true),
            'perm_rewards' => (bool) Setting::getValue('permissions', 'rewards', true),

            // Transaction & History
            'perm_transaction_history' => (bool) Setting::getValue('permissions', 'transaction_history', true),
            'perm_statements' => (bool) Setting::getValue('permissions', 'statements', true),

            // Support
            'perm_support_tickets' => (bool) Setting::getValue('permissions', 'support_tickets', true),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->persistTabInQueryString()
                    ->contained(false)
                    ->schema([
                        Tab::make('General')
                            ->icon('heroicon-o-building-office-2')
                            ->schema($this->getGeneralSchema()),

                        Tab::make('Branding')
                            ->icon('heroicon-o-photo')
                            ->schema($this->getBrandingSchema()),

                        Tab::make('SMTP / Email')
                            ->icon('heroicon-o-envelope')
                            ->schema($this->getSmtpSchema()),

                        Tab::make('Banking')
                            ->icon('heroicon-o-building-library')
                            ->schema($this->getBankingSchema()),

                        Tab::make('Limits & Fees')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema($this->getLimitsSchema()),

                        Tab::make('Security')
                            ->icon('heroicon-o-shield-check')
                            ->schema($this->getSecuritySchema()),

                        Tab::make('Notifications')
                            ->icon('heroicon-o-bell')
                            ->schema($this->getNotificationsSchema()),

                        Tab::make('Deposits')
                            ->icon('heroicon-o-banknotes')
                            ->schema($this->getDepositsSchema()),

                        Tab::make('Loans & Grants')
                            ->icon('heroicon-o-document-text')
                            ->schema($this->getLoansAndGrantsSchema()),

                        Tab::make('Cards')
                            ->icon('heroicon-o-credit-card')
                            ->schema($this->getCardsSchema()),

                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema($this->getSeoSchema()),

                        Tab::make('Maintenance')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema($this->getMaintenanceSchema()),

                        Tab::make('Permissions')
                            ->icon('heroicon-o-key')
                            ->schema($this->getPermissionsSchema()),

                        Tab::make('System Info')
                            ->icon('heroicon-o-server')
                            ->schema($this->getSystemInfoSchema()),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getGeneralSchema(): array
    {
        return [
            Section::make('Site Information')
                ->description('Basic information about your banking platform')
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Finora Bank'),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->placeholder('Your Trusted Banking Partner'),
                    ]),
                    Textarea::make('site_description')
                        ->label('Site Description')
                        ->rows(3)
                        ->placeholder('Brief description of your banking platform...'),
                ]),

            Section::make('Contact Information')
                ->description('How customers can reach you')
                ->icon('heroicon-o-phone')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('site_email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->placeholder('info@finorabank.com'),
                        TextInput::make('site_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->placeholder('+1 (800) 555-0199'),
                    ]),
                    Textarea::make('site_address')
                        ->label('Business Address')
                        ->rows(2)
                        ->placeholder('123 Banking Street, Financial District...'),
                ]),

            Section::make('Regional Settings')
                ->description('Currency and timezone configuration')
                ->icon('heroicon-o-globe-alt')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('site_currency')
                            ->label('Default Currency')
                            ->options([
                                'USD' => 'USD - US Dollar ($)',
                                'EUR' => 'EUR - Euro (€)',
                                'GBP' => 'GBP - British Pound (£)',
                                'CAD' => 'CAD - Canadian Dollar (C$)',
                                'AUD' => 'AUD - Australian Dollar (A$)',
                                'NGN' => 'NGN - Nigerian Naira (₦)',
                                'INR' => 'INR - Indian Rupee (₹)',
                                'JPY' => 'JPY - Japanese Yen (¥)',
                                'CHF' => 'CHF - Swiss Franc (CHF)',
                            ])
                            ->searchable()
                            ->required(),
                        Select::make('site_timezone')
                            ->label('Timezone')
                            ->options([
                                'UTC' => 'UTC (Coordinated Universal Time)',
                                'America/New_York' => 'Eastern Time (US & Canada)',
                                'America/Chicago' => 'Central Time (US & Canada)',
                                'America/Denver' => 'Mountain Time (US & Canada)',
                                'America/Los_Angeles' => 'Pacific Time (US & Canada)',
                                'Europe/London' => 'London (GMT/BST)',
                                'Europe/Paris' => 'Paris (CET/CEST)',
                                'Europe/Berlin' => 'Berlin (CET/CEST)',
                                'Asia/Dubai' => 'Dubai (GST)',
                                'Asia/Singapore' => 'Singapore (SGT)',
                                'Asia/Tokyo' => 'Tokyo (JST)',
                                'Africa/Lagos' => 'Lagos (WAT)',
                                'Australia/Sydney' => 'Sydney (AEST/AEDT)',
                            ])
                            ->searchable()
                            ->required(),
                    ]),
                ]),
        ];
    }

    protected function getBrandingSchema(): array
    {
        return [
            Section::make('Logo & Favicon')
                ->description('Upload your brand images')
                ->icon('heroicon-o-photo')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        FileUpload::make('site_logo')
                            ->label('Site Logo (Light Mode)')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio('3:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('100')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Recommended: 300x100px, PNG with transparency'),
                        FileUpload::make('site_logo_dark')
                            ->label('Site Logo (Dark Mode)')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio('3:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('100')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->maxSize(2048)
                            ->helperText('Optional: Logo for dark backgrounds'),
                        FileUpload::make('site_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('64')
                            ->imageResizeTargetHeight('64')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'image/svg+xml'])
                            ->maxSize(512)
                            ->helperText('Recommended: 64x64px, PNG or ICO'),
                    ]),
                ]),

            Section::make('Brand Colors')
                ->description('Define your brand color scheme')
                ->icon('heroicon-o-swatch')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('primary_color')
                            ->label('Primary Color')
                            ->type('color')
                            ->default('#3b82f6')
                            ->helperText('Main brand color used for buttons and links'),
                        TextInput::make('secondary_color')
                            ->label('Secondary Color')
                            ->type('color')
                            ->default('#1e40af')
                            ->helperText('Accent color for hover states and highlights'),
                    ]),
                ]),
        ];
    }

    protected function getSmtpSchema(): array
    {
        return [
            Section::make('Current Mail Configuration')
                ->description('Shows the active email configuration being used by the system')
                ->icon('heroicon-o-check-circle')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('current_mailer')
                            ->label('Active Mail Driver')
                            ->content(fn () => config('mail.default', 'Not configured')),
                        Placeholder::make('current_host')
                            ->label('SMTP Host')
                            ->content(fn () => config('mail.mailers.smtp.host') ?: 'Not configured'),
                        Placeholder::make('current_port')
                            ->label('SMTP Port')
                            ->content(fn () => config('mail.mailers.smtp.port') ?: 'Not configured'),
                        Placeholder::make('current_from')
                            ->label('From Address')
                            ->content(fn () => config('mail.from.address') ?: 'Not configured'),
                    ]),
                ]),

            Section::make('Mail Driver')
                ->description('Select and configure your email delivery method')
                ->icon('heroicon-o-server')
                ->collapsible()
                ->schema([
                    Select::make('mail_mailer')
                        ->label('Mail Driver')
                        ->options([
                            'smtp' => 'SMTP Server',
                            'sendmail' => 'Sendmail',
                            'mailgun' => 'Mailgun API',
                            'ses' => 'Amazon SES',
                            'postmark' => 'Postmark',
                            'resend' => 'Resend',
                            'log' => 'Log (Testing Only)',
                        ])
                        ->required()
                        ->live()
                        ->helperText('Choose how emails will be sent from your application'),
                ]),

            Section::make('SMTP Configuration')
                ->description('Configure your SMTP server settings')
                ->icon('heroicon-o-envelope')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.example.com')
                            ->helperText('e.g., smtp.gmail.com, smtp.mailgun.org'),
                        TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->placeholder('587')
                            ->numeric()
                            ->helperText('Common ports: 25, 465 (SSL), 587 (TLS)'),
                        TextInput::make('mail_username')
                            ->label('SMTP Username')
                            ->placeholder('your@email.com')
                            ->autocomplete('off'),
                        TextInput::make('mail_password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password'),
                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS (Recommended)',
                                'ssl' => 'SSL',
                                '' => 'None (Not Recommended)',
                            ])
                            ->default('tls'),
                    ]),
                ]),

            Section::make('Sender Information')
                ->description('Default sender details for outgoing emails')
                ->icon('heroicon-o-user')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('mail_from_address')
                            ->label('From Email Address')
                            ->email()
                            ->placeholder('noreply@finorabank.com')
                            ->helperText('The email address shown as the sender'),
                        TextInput::make('mail_from_name')
                            ->label('From Name')
                            ->placeholder('Finora Bank')
                            ->helperText('The name shown as the sender'),
                    ]),
                ]),
        ];
    }

    protected function getBankingSchema(): array
    {
        return [
            Section::make('Bank Identification')
                ->description('Core banking identifiers and routing codes')
                ->icon('heroicon-o-identification')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->required()
                            ->placeholder('Finora Bank'),
                        TextInput::make('bank_code')
                            ->label('Bank Code')
                            ->placeholder('FNRA')
                            ->helperText('Short code identifying your bank'),
                        TextInput::make('swift_code')
                            ->label('SWIFT/BIC Code')
                            ->placeholder('FNRAUS33')
                            ->helperText('For international wire transfers'),
                        TextInput::make('routing_number')
                            ->label('Routing Number (ABA)')
                            ->placeholder('021000021')
                            ->helperText('9-digit US routing number'),
                    ]),
                ]),

            Section::make('Transfer Services')
                ->description('Enable or disable different transfer types')
                ->icon('heroicon-o-arrows-right-left')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('wire_transfer_enabled')
                            ->label('Wire Transfers')
                            ->helperText('International bank transfers'),
                        Toggle::make('domestic_transfer_enabled')
                            ->label('Domestic Transfers')
                            ->helperText('Transfers to other local banks'),
                        Toggle::make('internal_transfer_enabled')
                            ->label('Internal Transfers')
                            ->helperText('Transfers within Finora Bank'),
                    ]),
                ]),
        ];
    }

    protected function getLimitsSchema(): array
    {
        return [
            Section::make('Wire Transfer Limits & Fees')
                ->description('International wire transfers via SWIFT')
                ->icon('heroicon-o-globe-alt')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('wire_daily_limit')
                            ->label('Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('100000')
                            ->helperText('Maximum total per day'),
                        TextInput::make('wire_per_transaction_limit')
                            ->label('Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('50000')
                            ->helperText('Maximum amount per transaction'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('wire_flat_fee')
                            ->label('Flat Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('25')
                            ->helperText('Fixed fee per transaction'),
                        TextInput::make('wire_percentage_fee')
                            ->label('Percentage Fee')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('0.5')
                            ->step('0.01')
                            ->helperText('Percentage of transfer amount'),
                    ]),
                ]),

            Section::make('Domestic Transfer Limits & Fees')
                ->description('Transfers to other local banks')
                ->icon('heroicon-o-building-library')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('domestic_daily_limit')
                            ->label('Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('50000')
                            ->helperText('Maximum total per day'),
                        TextInput::make('domestic_per_transaction_limit')
                            ->label('Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('25000')
                            ->helperText('Maximum amount per transaction'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('domestic_flat_fee')
                            ->label('Flat Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('5')
                            ->helperText('Fixed fee per transaction'),
                        TextInput::make('domestic_percentage_fee')
                            ->label('Percentage Fee')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('0')
                            ->step('0.01')
                            ->helperText('Percentage of transfer amount'),
                    ]),
                ]),

            Section::make('Internal Transfer Limits & Fees')
                ->description('Transfers within Finora Bank')
                ->icon('heroicon-o-arrows-right-left')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('internal_daily_limit')
                            ->label('Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('100000')
                            ->helperText('Maximum total per day'),
                        TextInput::make('internal_per_transaction_limit')
                            ->label('Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('50000')
                            ->helperText('Maximum amount per transaction'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('internal_flat_fee')
                            ->label('Flat Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('0')
                            ->helperText('Fixed fee per transaction'),
                        TextInput::make('internal_percentage_fee')
                            ->label('Percentage Fee')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('0')
                            ->step('0.01')
                            ->helperText('Percentage of transfer amount'),
                    ]),
                ]),

            Section::make('Card Fees')
                ->description('Fees for card-related services')
                ->icon('heroicon-o-credit-card')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('card_issuance_fee')
                            ->label('New Card Issuance Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('10'),
                        TextInput::make('card_replacement_fee')
                            ->label('Card Replacement Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('15'),
                    ]),
                ]),
        ];
    }

    protected function getSecuritySchema(): array
    {
        return [
            Section::make('Authentication Requirements')
                ->description('User authentication and verification settings')
                ->icon('heroicon-o-lock-closed')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('two_factor_enabled')
                            ->label('Two-Factor Authentication')
                            ->helperText('Require 2FA for all users'),
                        Toggle::make('email_verification_required')
                            ->label('Email Verification')
                            ->helperText('Verify email on registration'),
                        Toggle::make('kyc_required')
                            ->label('KYC Verification')
                            ->helperText('Require identity verification'),
                    ]),
                ]),

            Section::make('Password Policy')
                ->description('Password strength requirements')
                ->icon('heroicon-o-key')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('password_min_length')
                            ->label('Minimum Password Length')
                            ->numeric()
                            ->minValue(6)
                            ->maxValue(32)
                            ->suffix('characters')
                            ->placeholder('8'),
                        Toggle::make('require_special_char')
                            ->label('Require Special Character')
                            ->helperText('e.g., !@#$%^&*'),
                        Toggle::make('require_uppercase')
                            ->label('Require Uppercase Letter')
                            ->helperText('At least one A-Z'),
                    ]),
                ]),

            Section::make('Session & Lockout')
                ->description('Session management and brute force protection')
                ->icon('heroicon-o-clock')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('session_lifetime')
                            ->label('Session Lifetime')
                            ->numeric()
                            ->suffix('minutes')
                            ->placeholder('120')
                            ->helperText('Auto-logout after inactivity'),
                        TextInput::make('max_login_attempts')
                            ->label('Max Login Attempts')
                            ->numeric()
                            ->placeholder('5')
                            ->helperText('Before account lockout'),
                        TextInput::make('lockout_duration')
                            ->label('Lockout Duration')
                            ->numeric()
                            ->suffix('minutes')
                            ->placeholder('30')
                            ->helperText('Account lockout time'),
                    ]),
                ]),

            Section::make('Login Verification')
                ->description('Configure multi-step login verification requirements')
                ->icon('heroicon-o-shield-check')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('login_require_email_otp')
                            ->label('Require Email OTP on Login')
                            ->helperText('Users must verify email OTP code after password login')
                            ->default(true),
                        Toggle::make('login_require_pin')
                            ->label('Require PIN Verification on Login')
                            ->helperText('Users must enter transaction PIN after email verification')
                            ->default(true),
                    ]),
                    Placeholder::make('login_verification_note')
                        ->label('')
                        ->content('💡 **Note:** Individual users can be exempted from Email OTP verification in User Management → Edit User → "Skip Email OTP Verification" toggle.')
                        ->columnSpanFull(),
                ]),

            Section::make('Email OTP Login')
                ->description('Require email one-time password verification during login')
                ->icon('heroicon-o-envelope')
                ->collapsible()
                ->schema([
                    Toggle::make('email_otp_enabled')
                        ->label('Enable Email OTP')
                        ->helperText('Master switch for email OTP authentication')
                        ->live(),
                    Grid::make(2)->schema([
                        Toggle::make('email_otp_enforce_admin')
                            ->label('Enforce for Admin')
                            ->helperText('Require OTP for admin panel login')
                            ->visible(fn (Get $get) => $get('email_otp_enabled')),
                        Toggle::make('email_otp_enforce_user')
                            ->label('Enforce for Users')
                            ->helperText('Require OTP for user dashboard login')
                            ->visible(fn (Get $get) => $get('email_otp_enabled')),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('email_otp_expiry')
                            ->label('OTP Expiry Time')
                            ->numeric()
                            ->suffix('minutes')
                            ->placeholder('10')
                            ->helperText('How long the OTP remains valid')
                            ->visible(fn (Get $get) => $get('email_otp_enabled')),
                        TextInput::make('email_otp_length')
                            ->label('OTP Length')
                            ->numeric()
                            ->minValue(4)
                            ->maxValue(8)
                            ->suffix('digits')
                            ->placeholder('6')
                            ->helperText('Number of digits in OTP code')
                            ->visible(fn (Get $get) => $get('email_otp_enabled')),
                    ]),
                ]),

            Section::make('Transfer OTP Verification')
                ->description('Require email OTP verification for transfer transactions')
                ->icon('heroicon-o-paper-airplane')
                ->collapsible()
                ->schema([
                    Toggle::make('transfer_otp_enabled')
                        ->label('Enable Transfer OTP')
                        ->helperText('When enabled, users must verify transfers with email OTP after PIN verification. This is a global setting that can be overridden per user.'),
                ]),

            Section::make('Google reCAPTCHA')
                ->description('Protect login forms from bots and automated attacks')
                ->icon('heroicon-o-shield-exclamation')
                ->collapsible()
                ->schema([
                    Toggle::make('recaptcha_enabled')
                        ->label('Enable reCAPTCHA')
                        ->helperText('Master switch for Google reCAPTCHA protection')
                        ->live(),
                    Grid::make(2)->schema([
                        Toggle::make('recaptcha_enforce_admin')
                            ->label('Enforce for Admin')
                            ->helperText('Show reCAPTCHA on admin login')
                            ->visible(fn (Get $get) => $get('recaptcha_enabled')),
                        Toggle::make('recaptcha_enforce_user')
                            ->label('Enforce for Users')
                            ->helperText('Show reCAPTCHA on user login/register')
                            ->visible(fn (Get $get) => $get('recaptcha_enabled')),
                    ]),
                    Select::make('recaptcha_version')
                        ->label('reCAPTCHA Version')
                        ->options([
                            'v2' => 'reCAPTCHA v2 (Checkbox)',
                            'v2_invisible' => 'reCAPTCHA v2 (Invisible)',
                            'v3' => 'reCAPTCHA v3 (Score-based)',
                        ])
                        ->default('v2')
                        ->helperText('Choose the reCAPTCHA version to use')
                        ->visible(fn (Get $get) => $get('recaptcha_enabled'))
                        ->live(),
                    Grid::make(2)->schema([
                        TextInput::make('recaptcha_site_key')
                            ->label('Site Key')
                            ->placeholder('Enter your reCAPTCHA site key')
                            ->helperText('Public key from Google reCAPTCHA console')
                            ->visible(fn (Get $get) => $get('recaptcha_enabled')),
                        TextInput::make('recaptcha_secret_key')
                            ->label('Secret Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Enter your reCAPTCHA secret key')
                            ->helperText('Private key from Google reCAPTCHA console')
                            ->visible(fn (Get $get) => $get('recaptcha_enabled')),
                    ]),
                    TextInput::make('recaptcha_score_threshold')
                        ->label('Score Threshold (v3 only)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step(0.1)
                        ->placeholder('0.5')
                        ->helperText('Minimum score to pass (0.0 = bot, 1.0 = human). Recommended: 0.5')
                        ->visible(fn (Get $get) => $get('recaptcha_enabled') && $get('recaptcha_version') === 'v3'),
                    Placeholder::make('recaptcha_help')
                        ->content('Get your reCAPTCHA keys from: https://www.google.com/recaptcha/admin')
                        ->visible(fn (Get $get) => $get('recaptcha_enabled')),
                ]),
        ];
    }

    protected function getNotificationsSchema(): array
    {
        return [
            Section::make('Notification Channels')
                ->description('Enable different notification delivery methods')
                ->icon('heroicon-o-bell-alert')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->helperText('Send notifications via email'),
                        Toggle::make('sms_notifications')
                            ->label('SMS Notifications')
                            ->helperText('Send notifications via SMS'),
                        Toggle::make('push_notifications')
                            ->label('Push Notifications')
                            ->helperText('Browser/app push notifications'),
                    ]),
                ]),

            Section::make('Alert Types')
                ->description('Configure which events trigger notifications')
                ->icon('heroicon-o-exclamation-triangle')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('transaction_alerts')
                            ->label('Transaction Alerts')
                            ->helperText('Deposits, withdrawals, transfers'),
                        Toggle::make('login_alerts')
                            ->label('Login Alerts')
                            ->helperText('New login notifications'),
                        Toggle::make('marketing_emails')
                            ->label('Marketing Emails')
                            ->helperText('Promotional communications'),
                    ]),
                ]),
        ];
    }

    protected function getDepositsSchema(): array
    {
        return [
            Section::make('Deposit Methods')
                ->description('Enable or disable deposit types')
                ->icon('heroicon-o-inbox-arrow-down')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('check_deposit_enabled')
                            ->label('Check Deposits')
                            ->helperText('Allow check image deposits'),
                        Toggle::make('mobile_deposit_enabled')
                            ->label('Mobile/Gateway Deposits')
                            ->helperText('Payment gateway deposits'),
                        Toggle::make('crypto_deposit_enabled')
                            ->label('Crypto Deposits')
                            ->helperText('Cryptocurrency deposits'),
                    ]),
                ]),

            Section::make('Deposit Settings')
                ->description('Configure deposit processing rules')
                ->icon('heroicon-o-adjustments-horizontal')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('check_hold_period')
                            ->label('Check Hold Period')
                            ->numeric()
                            ->suffix('days')
                            ->placeholder('3')
                            ->helperText('Days before check funds are available'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('check_daily_limit')
                            ->label('Check Deposit Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('5000')
                            ->helperText('Maximum total check deposits per day per user'),
                        TextInput::make('check_per_transaction_limit')
                            ->label('Check Deposit Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('2500')
                            ->helperText('Maximum amount per single check deposit'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('mobile_daily_limit')
                            ->label('Mobile Deposit Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('10000')
                            ->helperText('Maximum total deposits per day per user'),
                        TextInput::make('mobile_per_transaction_limit')
                            ->label('Mobile Deposit Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('5000')
                            ->helperText('Maximum amount per single deposit transaction'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('crypto_daily_limit')
                            ->label('Crypto Deposit Daily Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('10000')
                            ->helperText('Maximum total crypto deposits per day per user'),
                        TextInput::make('crypto_per_transaction_limit')
                            ->label('Crypto Deposit Per Transaction Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('5000')
                            ->helperText('Maximum amount per single crypto deposit'),
                    ]),
                ]),
        ];
    }

    protected function getLoansAndGrantsSchema(): array
    {
        return [
            // ==================== LOAN SETTINGS ====================
            Section::make('Loan Services')
                ->description('Enable or disable loan features')
                ->icon('heroicon-o-document-check')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('loans_enabled')
                            ->label('Enable Loan Services')
                            ->helperText('Master switch for all loan features'),
                        Toggle::make('loan_applications_enabled')
                            ->label('Accept Loan Applications')
                            ->helperText('Allow new loan applications')
                            ->visible(fn (Get $get) => $get('loans_enabled')),
                    ]),
                    Grid::make(3)->schema([
                        Toggle::make('auto_approve_loans')
                            ->label('Auto-Approve Loans')
                            ->helperText('Automatically approve qualifying loans')
                            ->visible(fn (Get $get) => $get('loans_enabled')),
                        Toggle::make('require_collateral')
                            ->label('Require Collateral')
                            ->helperText('Require collateral for loan approval')
                            ->visible(fn (Get $get) => $get('loans_enabled')),
                        Toggle::make('require_guarantor')
                            ->label('Require Guarantor')
                            ->helperText('Require a guarantor for loan approval')
                            ->visible(fn (Get $get) => $get('loans_enabled')),
                    ]),
                ]),

            Section::make('Loan Verification Requirements')
                ->description('Documents and verification needed for loan applications')
                ->icon('heroicon-o-shield-check')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('loans_enabled'))
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('require_employment_verification')
                            ->label('Employment Verification')
                            ->helperText('Verify applicant employment status'),
                        Toggle::make('require_income_verification')
                            ->label('Income Verification')
                            ->helperText('Verify applicant income documents'),
                    ]),
                    TextInput::make('min_credit_score')
                        ->label('Minimum Credit Score')
                        ->numeric()
                        ->placeholder('650')
                        ->helperText('Minimum credit score required for loan approval'),
                ]),

            Section::make('Loan Limits')
                ->description('Loan amount and term restrictions')
                ->icon('heroicon-o-calculator')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('loans_enabled'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('min_loan_amount')
                            ->label('Minimum Loan Amount')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('1000'),
                        TextInput::make('max_loan_amount')
                            ->label('Maximum Loan Amount')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('500000'),
                        TextInput::make('min_loan_term')
                            ->label('Minimum Loan Term')
                            ->numeric()
                            ->suffix('months')
                            ->placeholder('6'),
                        TextInput::make('max_loan_term')
                            ->label('Maximum Loan Term')
                            ->numeric()
                            ->suffix('months')
                            ->placeholder('360'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('default_interest_rate')
                            ->label('Default Interest Rate')
                            ->numeric()
                            ->suffix('% p.a.')
                            ->placeholder('8.5'),
                        TextInput::make('max_active_loans')
                            ->label('Max Active Loans Per User')
                            ->numeric()
                            ->placeholder('3')
                            ->helperText('Maximum number of concurrent loans per user'),
                    ]),
                ]),

            Section::make('Loan Fees & Penalties')
                ->description('Fee structure for loans')
                ->icon('heroicon-o-currency-dollar')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('loans_enabled'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('loan_origination_fee')
                            ->label('Origination Fee')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('1')
                            ->helperText('Percentage of loan amount'),
                        TextInput::make('late_payment_fee')
                            ->label('Late Payment Fee')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('35')
                            ->helperText('Flat fee for missed payments'),
                        TextInput::make('grace_period_days')
                            ->label('Grace Period')
                            ->numeric()
                            ->suffix('days')
                            ->placeholder('15')
                            ->helperText('Days before late fee applies'),
                    ]),
                ]),

            // ==================== GRANT SETTINGS ====================
            Section::make('Grant Services')
                ->description('Enable or disable grant features')
                ->icon('heroicon-o-gift')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('grants_enabled')
                            ->label('Enable Grant Services')
                            ->helperText('Master switch for all grant features'),
                        Toggle::make('grant_applications_enabled')
                            ->label('Accept Grant Applications')
                            ->helperText('Allow new grant applications')
                            ->visible(fn (Get $get) => $get('grants_enabled')),
                    ]),
                    Grid::make(2)->schema([
                        Toggle::make('auto_approve_grants')
                            ->label('Auto-Approve Grants')
                            ->helperText('Automatically approve qualifying grant applications')
                            ->visible(fn (Get $get) => $get('grants_enabled')),
                        Toggle::make('notify_on_grant_status')
                            ->label('Notify on Status Change')
                            ->helperText('Send email when application status changes')
                            ->visible(fn (Get $get) => $get('grants_enabled')),
                    ]),
                ]),

            Section::make('Grant Application Requirements')
                ->description('Requirements for grant applications')
                ->icon('heroicon-o-clipboard-document-check')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('grants_enabled'))
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('require_grant_documents')
                            ->label('Require Documents')
                            ->helperText('Require document uploads for grant applications'),
                        Toggle::make('require_eligibility_check')
                            ->label('Require Eligibility Check')
                            ->helperText('Verify eligibility criteria before submission'),
                    ]),
                    Grid::make(2)->schema([
                        Toggle::make('allow_grant_reapplication')
                            ->label('Allow Re-application')
                            ->helperText('Allow users to re-apply after rejection'),
                    ]),
                ]),

            Section::make('Grant Limits')
                ->description('Grant application restrictions')
                ->icon('heroicon-o-adjustments-horizontal')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('grants_enabled'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('max_grant_applications_per_user')
                            ->label('Max Applications Per User')
                            ->numeric()
                            ->placeholder('5')
                            ->helperText('Maximum grant applications per user'),
                        TextInput::make('grant_application_cooldown')
                            ->label('Application Cooldown')
                            ->numeric()
                            ->suffix('days')
                            ->placeholder('30')
                            ->helperText('Days before user can apply for same grant'),
                    ]),
                ]),
        ];
    }

    protected function getCardsSchema(): array
    {
        return [
            Section::make('Card Types')
                ->description('Available card options for customers')
                ->icon('heroicon-o-credit-card')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('virtual_cards_enabled')
                            ->label('Virtual Cards')
                            ->helperText('Allow virtual card creation'),
                        Toggle::make('physical_cards_enabled')
                            ->label('Physical Cards')
                            ->helperText('Allow physical card requests'),
                    ]),
                ]),

            Section::make('Card Spending Limits')
                ->description('Default spending limits for new cards')
                ->icon('heroicon-o-arrow-trending-up')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('card_daily_limit')
                            ->label('Daily Spending Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('5000'),
                        TextInput::make('card_monthly_limit')
                            ->label('Monthly Spending Limit')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('50000'),
                    ]),
                ]),
        ];
    }

    protected function getSeoSchema(): array
    {
        return [
            Section::make('Meta Tags')
                ->description('Search engine optimization settings for your site')
                ->icon('heroicon-o-tag')
                ->collapsible()
                ->schema([
                    TextInput::make('seo_meta_title')
                        ->label('Meta Title')
                        ->placeholder('Finora Bank - Online Banking Made Simple')
                        ->helperText('Default title for pages (50-60 characters recommended)')
                        ->maxLength(70),
                    Textarea::make('seo_meta_description')
                        ->label('Meta Description')
                        ->rows(3)
                        ->placeholder('Experience secure and convenient online banking with Finora Bank...')
                        ->helperText('Default description for search results (150-160 characters recommended)')
                        ->maxLength(200),
                    TextInput::make('seo_meta_keywords')
                        ->label('Meta Keywords')
                        ->placeholder('online banking, secure banking, money transfer, loans')
                        ->helperText('Comma-separated keywords (less important for modern SEO)'),
                ]),

            Section::make('Open Graph (Social Media)')
                ->description('Control how your site appears when shared on social media')
                ->icon('heroicon-o-share')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('seo_og_title')
                        ->label('OG Title')
                        ->placeholder('Finora Bank - Your Trusted Banking Partner')
                        ->helperText('Title shown when shared on Facebook, LinkedIn, etc.'),
                    Textarea::make('seo_og_description')
                        ->label('OG Description')
                        ->rows(2)
                        ->placeholder('Join millions who trust Finora Bank for their banking needs.')
                        ->helperText('Description shown on social media previews'),
                    FileUpload::make('seo_og_image')
                        ->label('OG Image')
                        ->image()
                        ->disk('public')
                        ->directory('seo')
                        ->helperText('Recommended size: 1200x630 pixels'),
                ]),

            Section::make('Twitter Card')
                ->description('Twitter-specific sharing settings')
                ->icon('heroicon-o-chat-bubble-left')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('seo_twitter_card')
                        ->label('Twitter Card Type')
                        ->options([
                            'summary' => 'Summary',
                            'summary_large_image' => 'Summary with Large Image',
                        ])
                        ->default('summary_large_image'),
                    TextInput::make('seo_twitter_site')
                        ->label('Twitter Username')
                        ->placeholder('@finorabank')
                        ->helperText('Your Twitter/X handle including @'),
                ]),

            Section::make('Analytics & Tracking')
                ->description('Third-party analytics and tracking codes')
                ->icon('heroicon-o-chart-bar')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('seo_google_analytics')
                        ->label('Google Analytics ID')
                        ->placeholder('G-XXXXXXXXXX or UA-XXXXXXXXX-X')
                        ->helperText('Your Google Analytics measurement ID'),
                    TextInput::make('seo_google_tag_manager')
                        ->label('Google Tag Manager ID')
                        ->placeholder('GTM-XXXXXXX')
                        ->helperText('Your GTM container ID'),
                    TextInput::make('seo_facebook_pixel')
                        ->label('Facebook Pixel ID')
                        ->placeholder('XXXXXXXXXXXXXXXX')
                        ->helperText('Your Facebook Pixel ID for tracking'),
                ]),

            Section::make('Advanced SEO')
                ->description('Advanced settings for SEO experts')
                ->icon('heroicon-o-code-bracket')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Textarea::make('seo_robots_txt')
                        ->label('Robots.txt Content')
                        ->rows(5)
                        ->placeholder("User-agent: *\nAllow: /")
                        ->helperText('Content for your robots.txt file'),
                    Textarea::make('seo_custom_head_code')
                        ->label('Custom Head Code')
                        ->rows(5)
                        ->placeholder('<!-- Custom scripts, meta tags, etc. -->')
                        ->helperText('Custom HTML/JS to inject in the <head> section'),
                ]),
        ];
    }

    protected function getMaintenanceSchema(): array
    {
        return [
            Section::make('Maintenance Mode')
                ->description('Enable maintenance mode to restrict access')
                ->icon('heroicon-o-wrench-screwdriver')
                ->collapsible()
                ->schema([
                    Toggle::make('maintenance_mode')
                        ->label('Enable Maintenance Mode')
                        ->helperText('When enabled, only administrators can access the site')
                        ->live(),
                    Textarea::make('maintenance_message')
                        ->label('Maintenance Message')
                        ->rows(3)
                        ->placeholder('We are currently performing maintenance. Please check back later.')
                        ->helperText('Message shown to users during maintenance')
                        ->visible(fn (Get $get) => $get('maintenance_mode')),
                    TextInput::make('maintenance_allowed_ips')
                        ->label('Allowed IP Addresses')
                        ->placeholder('192.168.1.1, 10.0.0.1')
                        ->helperText('Comma-separated IPs that can bypass maintenance mode')
                        ->visible(fn (Get $get) => $get('maintenance_mode')),
                    TextInput::make('maintenance_secret')
                        ->label('Bypass Secret')
                        ->placeholder('my-secret-key')
                        ->helperText('Secret URL parameter to bypass maintenance (e.g., ?secret=my-secret-key)')
                        ->visible(fn (Get $get) => $get('maintenance_mode')),
                ]),

            Section::make('Cache Management')
                ->description('Clear various system caches')
                ->icon('heroicon-o-trash')
                ->collapsible()
                ->schema([
                    Placeholder::make('cache_info')
                        ->content('Use the buttons below to clear different types of cache. This can help resolve issues after updates.'),
                    Grid::make(4)->schema([
                        Placeholder::make('clear_app_cache')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'clearAppCache',
                                'label' => 'Clear App Cache',
                                'icon' => 'heroicon-o-archive-box-x-mark',
                                'color' => 'danger',
                            ])),
                        Placeholder::make('clear_view_cache')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'clearViewCache',
                                'label' => 'Clear View Cache',
                                'icon' => 'heroicon-o-eye-slash',
                                'color' => 'warning',
                            ])),
                        Placeholder::make('clear_route_cache')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'clearRouteCache',
                                'label' => 'Clear Route Cache',
                                'icon' => 'heroicon-o-map',
                                'color' => 'info',
                            ])),
                        Placeholder::make('clear_config_cache')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'clearConfigCache',
                                'label' => 'Clear Config Cache',
                                'icon' => 'heroicon-o-cog-6-tooth',
                                'color' => 'gray',
                            ])),
                    ]),
                ]),

            Section::make('Optimization')
                ->description('Optimize your application for better performance')
                ->icon('heroicon-o-bolt')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Placeholder::make('optimization_info')
                        ->content('These actions will cache routes, config, and views for better performance. Use in production environments.'),
                    Grid::make(2)->schema([
                        Placeholder::make('optimize_app')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'optimizeApp',
                                'label' => 'Optimize Application',
                                'icon' => 'heroicon-o-rocket-launch',
                                'color' => 'success',
                            ])),
                        Placeholder::make('clear_all_cache')
                            ->content(fn () => view('filament.components.action-button', [
                                'action' => 'clearAllCache',
                                'label' => 'Clear All Caches',
                                'icon' => 'heroicon-o-trash',
                                'color' => 'danger',
                            ])),
                    ]),
                ]),
        ];
    }

    protected function getSystemInfoSchema(): array
    {
        return [
            Section::make('Application Information')
                ->description('Details about your Finora Bank installation')
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('app_name')
                            ->label('Application Name')
                            ->content(fn () => config('app.name', 'Finora Bank')),
                        Placeholder::make('app_env')
                            ->label('Environment')
                            ->content(fn () => ucfirst(config('app.env', 'production'))),
                        Placeholder::make('app_debug')
                            ->label('Debug Mode')
                            ->content(fn () => config('app.debug') ? '✅ Enabled' : '❌ Disabled'),
                    ]),
                    Grid::make(3)->schema([
                        Placeholder::make('app_url')
                            ->label('Application URL')
                            ->content(fn () => config('app.url', 'Not set')),
                        Placeholder::make('app_timezone')
                            ->label('Timezone')
                            ->content(fn () => config('app.timezone', 'UTC')),
                        Placeholder::make('app_locale')
                            ->label('Locale')
                            ->content(fn () => config('app.locale', 'en')),
                    ]),
                ]),

            Section::make('Server Information')
                ->description('Details about the server environment')
                ->icon('heroicon-o-server-stack')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('php_version')
                            ->label('PHP Version')
                            ->content(fn () => PHP_VERSION),
                        Placeholder::make('laravel_version')
                            ->label('Laravel Version')
                            ->content(fn () => app()->version()),
                        Placeholder::make('filament_version')
                            ->label('Filament Version')
                            ->content(fn () => \Composer\InstalledVersions::getPrettyVersion('filament/filament') ?? 'Unknown'),
                    ]),
                    Grid::make(3)->schema([
                        Placeholder::make('server_software')
                            ->label('Server Software')
                            ->content(fn () => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
                        Placeholder::make('server_os')
                            ->label('Operating System')
                            ->content(fn () => PHP_OS_FAMILY.' ('.php_uname('r').')'),
                        Placeholder::make('server_protocol')
                            ->label('Protocol')
                            ->content(fn () => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown'),
                    ]),
                ]),

            Section::make('Database Information')
                ->description('Database connection details')
                ->icon('heroicon-o-circle-stack')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('db_driver')
                            ->label('Database Driver')
                            ->content(fn () => ucfirst(config('database.default', 'Unknown'))),
                        Placeholder::make('db_name')
                            ->label('Database Name')
                            ->content(fn () => config('database.connections.'.config('database.default').'.database', 'Unknown')),
                        Placeholder::make('db_host')
                            ->label('Database Host')
                            ->content(fn () => config('database.connections.'.config('database.default').'.host', 'N/A')),
                    ]),
                    Grid::make(2)->schema([
                        Placeholder::make('db_version')
                            ->label('Database Version')
                            ->content(function () {
                                try {
                                    $pdo = \DB::connection()->getPdo();

                                    return $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
                                } catch (\Exception $e) {
                                    return 'Unable to retrieve';
                                }
                            }),
                        Placeholder::make('db_tables')
                            ->label('Total Tables')
                            ->content(function () {
                                try {
                                    $tables = \DB::select('SELECT name FROM sqlite_master WHERE type="table"');

                                    return count($tables).' tables';
                                } catch (\Exception $e) {
                                    try {
                                        $tables = \DB::select('SHOW TABLES');

                                        return count($tables).' tables';
                                    } catch (\Exception $e) {
                                        return 'Unable to count';
                                    }
                                }
                            }),
                    ]),
                ]),

            Section::make('PHP Configuration')
                ->description('Important PHP settings')
                ->icon('heroicon-o-command-line')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('php_memory_limit')
                            ->label('Memory Limit')
                            ->content(fn () => ini_get('memory_limit')),
                        Placeholder::make('php_max_execution')
                            ->label('Max Execution Time')
                            ->content(fn () => ini_get('max_execution_time').' seconds'),
                        Placeholder::make('php_upload_max')
                            ->label('Max Upload Size')
                            ->content(fn () => ini_get('upload_max_filesize')),
                    ]),
                    Grid::make(3)->schema([
                        Placeholder::make('php_post_max')
                            ->label('Post Max Size')
                            ->content(fn () => ini_get('post_max_size')),
                        Placeholder::make('php_max_input_vars')
                            ->label('Max Input Vars')
                            ->content(fn () => ini_get('max_input_vars')),
                        Placeholder::make('php_extensions')
                            ->label('Loaded Extensions')
                            ->content(fn () => count(get_loaded_extensions()).' extensions'),
                    ]),
                ]),

            Section::make('Cache & Session')
                ->description('Caching and session configuration')
                ->icon('heroicon-o-archive-box')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('cache_driver')
                            ->label('Cache Driver')
                            ->content(fn () => ucfirst(config('cache.default', 'file'))),
                        Placeholder::make('session_driver')
                            ->label('Session Driver')
                            ->content(fn () => ucfirst(config('session.driver', 'file'))),
                        Placeholder::make('queue_driver')
                            ->label('Queue Driver')
                            ->content(fn () => ucfirst(config('queue.default', 'sync'))),
                    ]),
                    Grid::make(3)->schema([
                        Placeholder::make('mail_driver')
                            ->label('Mail Driver')
                            ->content(fn () => ucfirst(config('mail.default', 'smtp'))),
                        Placeholder::make('filesystem_driver')
                            ->label('Filesystem')
                            ->content(fn () => ucfirst(config('filesystems.default', 'local'))),
                        Placeholder::make('broadcast_driver')
                            ->label('Broadcast Driver')
                            ->content(fn () => ucfirst(config('broadcasting.default', 'null'))),
                    ]),
                ]),

            Section::make('Storage & Disk Space')
                ->description('Disk usage information')
                ->icon('heroicon-o-folder')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('disk_total')
                            ->label('Total Disk Space')
                            ->content(function () {
                                $bytes = disk_total_space(base_path());

                                return $this->formatBytes($bytes);
                            }),
                        Placeholder::make('disk_free')
                            ->label('Free Disk Space')
                            ->content(function () {
                                $bytes = disk_free_space(base_path());

                                return $this->formatBytes($bytes);
                            }),
                        Placeholder::make('disk_used')
                            ->label('Used Disk Space')
                            ->content(function () {
                                $total = disk_total_space(base_path());
                                $free = disk_free_space(base_path());

                                return $this->formatBytes($total - $free);
                            }),
                    ]),
                    Grid::make(2)->schema([
                        Placeholder::make('storage_size')
                            ->label('Storage Folder Size')
                            ->content(function () {
                                $path = storage_path();

                                return $this->formatBytes($this->getFolderSize($path));
                            }),
                        Placeholder::make('logs_size')
                            ->label('Logs Folder Size')
                            ->content(function () {
                                $path = storage_path('logs');

                                return $this->formatBytes($this->getFolderSize($path));
                            }),
                    ]),
                ]),

            Section::make('Developer Information')
                ->description('About the development team')
                ->icon('heroicon-o-code-bracket-square')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('dev_company')
                            ->label('Developed By')
                            ->content('Finora Bank Development Team'),
                        Placeholder::make('dev_version')
                            ->label('Application Version')
                            ->content(fn () => config('app.version', '1.0.0')),
                    ]),
                    Grid::make(2)->schema([
                        Placeholder::make('dev_website')
                            ->label('Website')
                            ->content(fn () => new \Illuminate\Support\HtmlString('<a href="https://finorabank.com" target="_blank" class="text-primary-500 hover:underline">https://finorabank.com</a>')),
                        Placeholder::make('dev_support')
                            ->label('Support Email')
                            ->content(fn () => new \Illuminate\Support\HtmlString('<a href="mailto:support@finorabank.com" class="text-primary-500 hover:underline">support@finorabank.com</a>')),
                    ]),
                    Placeholder::make('dev_copyright')
                        ->label('Copyright')
                        ->content(fn () => '© '.date('Y').' Finora Bank. All rights reserved.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    protected function getFolderSize(string $path): int
    {
        $size = 0;
        if (is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    protected function getPermissionsSchema(): array
    {
        return [
            Section::make('User Dashboard Feature Access')
                ->description('Control which features are visible and accessible to users in their dashboard. Disabling a feature will hide it from the user interface.')
                ->icon('heroicon-o-eye')
                ->schema([
                    Placeholder::make('permissions_info')
                        ->content('These settings control global feature visibility for all users. Individual user permissions can be managed from the Users section.')
                        ->columnSpanFull(),
                ]),

            Section::make('Account Management')
                ->description('Control access to account-related features')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_bank_accounts')
                            ->label('Bank Accounts')
                            ->helperText('View and manage bank accounts'),
                        Toggle::make('perm_beneficiaries')
                            ->label('Beneficiaries')
                            ->helperText('Manage saved beneficiaries'),
                        Toggle::make('perm_statements')
                            ->label('Account Statements')
                            ->helperText('Download account statements'),
                    ]),
                ]),

            Section::make('Transfer Services')
                ->description('Control access to money transfer features')
                ->icon('heroicon-o-arrows-right-left')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_wire_transfers')
                            ->label('Wire Transfers')
                            ->helperText('International SWIFT transfers'),
                        Toggle::make('perm_internal_transfers')
                            ->label('Internal Transfers')
                            ->helperText('User-to-user within bank'),
                        Toggle::make('perm_domestic_transfers')
                            ->label('Domestic Transfers')
                            ->helperText('Transfers to local banks'),
                    ]),
                ]),

            Section::make('Deposit Services')
                ->description('Control access to deposit methods')
                ->icon('heroicon-o-banknotes')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_check_deposits')
                            ->label('Check Deposits')
                            ->helperText('Deposit checks via image upload'),
                        Toggle::make('perm_mobile_deposits')
                            ->label('Mobile Deposits')
                            ->helperText('Deposit via payment gateways'),
                        Toggle::make('perm_crypto_deposits')
                            ->label('Crypto Deposits')
                            ->helperText('Cryptocurrency deposits'),
                    ]),
                ]),

            Section::make('Loans & Grants')
                ->description('Control access to loan and grant features')
                ->icon('heroicon-o-document-text')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_loans')
                            ->label('View Loans')
                            ->helperText('View active loans'),
                        Toggle::make('perm_loan_applications')
                            ->label('Loan Applications')
                            ->helperText('Apply for new loans'),
                        Toggle::make('perm_grants')
                            ->label('View Grants')
                            ->helperText('View available grants'),
                    ]),
                    Grid::make(3)->schema([
                        Toggle::make('perm_grant_applications')
                            ->label('Grant Applications')
                            ->helperText('Apply for grants'),
                        Toggle::make('perm_tax_refunds')
                            ->label('Tax Refunds (IRS)')
                            ->helperText('IRS tax refund services'),
                    ]),
                ]),

            Section::make('Card Services')
                ->description('Control access to card management features')
                ->icon('heroicon-o-credit-card')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_cards')
                            ->label('Card Management')
                            ->helperText('View and manage cards'),
                        Toggle::make('perm_virtual_cards')
                            ->label('Virtual Cards')
                            ->helperText('Create virtual cards'),
                        Toggle::make('perm_physical_cards')
                            ->label('Physical Cards')
                            ->helperText('Request physical cards'),
                    ]),
                ]),

            Section::make('Other Financial Services')
                ->description('Control access to additional financial features')
                ->icon('heroicon-o-currency-dollar')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('perm_withdrawals')
                            ->label('Withdrawals')
                            ->helperText('Withdraw funds'),
                        Toggle::make('perm_money_requests')
                            ->label('Money Requests')
                            ->helperText('Request money from others'),
                        Toggle::make('perm_exchange_money')
                            ->label('Currency Exchange')
                            ->helperText('Exchange between currencies'),
                    ]),
                    Grid::make(3)->schema([
                        Toggle::make('perm_vouchers')
                            ->label('Vouchers')
                            ->helperText('Voucher/gift card features'),
                        Toggle::make('perm_rewards')
                            ->label('Rewards Program')
                            ->helperText('Loyalty rewards system'),
                    ]),
                ]),

            Section::make('History & Records')
                ->description('Control access to transaction history and records')
                ->icon('heroicon-o-document-magnifying-glass')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('perm_transaction_history')
                            ->label('Transaction History')
                            ->helperText('View all transaction records'),
                    ]),
                ]),

            Section::make('Support & Help')
                ->description('Control access to support features')
                ->icon('heroicon-o-lifebuoy')
                ->collapsible()
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('perm_support_tickets')
                            ->label('Support Tickets')
                            ->helperText('Create and manage support tickets'),
                    ]),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save All Settings')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('save'),
            Action::make('testEmail')
                ->label('Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->modalWidth(Width::Medium)
                ->modalHeading('Send Test Email')
                ->modalDescription('Send a test email to verify your SMTP settings are working correctly.')
                ->form([
                    TextInput::make('test_email')
                        ->label('Test Email Address')
                        ->email()
                        ->required()
                        ->default(fn () => auth()->user()?->email),
                ])
                ->action(function (array $data) {
                    try {
                        // Apply current SMTP settings from database before sending
                        $this->applyMailConfig();

                        \Illuminate\Support\Facades\Mail::raw(
                            'This is a test email from Finora Bank to verify your SMTP settings are working correctly.'."\n\n".
                            'Configuration used:'."\n".
                            '- Mailer: '.config('mail.default')."\n".
                            '- Host: '.config('mail.mailers.smtp.host')."\n".
                            '- Port: '.config('mail.mailers.smtp.port')."\n".
                            '- From: '.config('mail.from.address'),
                            function ($message) use ($data) {
                                $message->to($data['test_email'])
                                    ->subject('Finora Bank - SMTP Test Email');
                            }
                        );

                        Notification::make()
                            ->title('Test Email Sent')
                            ->body("A test email has been sent to {$data['test_email']}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Email Failed')
                            ->body('Failed to send test email: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * Apply mail configuration from current form data.
     */
    protected function applyMailConfig(): void
    {
        $data = $this->form->getState();

        // Apply mailer
        if (! empty($data['mail_mailer'])) {
            \Illuminate\Support\Facades\Config::set('mail.default', $data['mail_mailer']);
        }

        // Apply SMTP settings
        if (! empty($data['mail_host'])) {
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.host', $data['mail_host']);
        }

        if (! empty($data['mail_port'])) {
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.port', (int) $data['mail_port']);
        }

        if (! empty($data['mail_username'])) {
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.username', $data['mail_username']);
        }

        if (! empty($data['mail_password'])) {
            \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.password', $data['mail_password']);
        }

        // Handle encryption (empty string means no encryption)
        $encryption = $data['mail_encryption'] ?? 'tls';
        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.encryption', $encryption === '' ? null : $encryption);

        // Apply from address
        if (! empty($data['mail_from_address'])) {
            \Illuminate\Support\Facades\Config::set('mail.from.address', $data['mail_from_address']);
        }

        if (! empty($data['mail_from_name'])) {
            \Illuminate\Support\Facades\Config::set('mail.from.name', $data['mail_from_name']);
        }

        // Purge the mailer so it picks up new config
        \Illuminate\Support\Facades\Mail::purge('smtp');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // General Settings
        Setting::setValue('general', 'site_name', $data['site_name'] ?? '', 'string');
        Setting::setValue('general', 'site_tagline', $data['site_tagline'] ?? '', 'string');
        Setting::setValue('general', 'site_description', $data['site_description'] ?? '', 'string');
        Setting::setValue('general', 'site_email', $data['site_email'] ?? '', 'string');
        Setting::setValue('general', 'site_phone', $data['site_phone'] ?? '', 'string');
        Setting::setValue('general', 'site_address', $data['site_address'] ?? '', 'string');
        Setting::setValue('general', 'site_currency', $data['site_currency'] ?? 'USD', 'string');
        Setting::setValue('general', 'site_timezone', $data['site_timezone'] ?? 'UTC', 'string');
        Setting::setValue('general', 'maintenance_mode', $data['maintenance_mode'] ?? false, 'boolean');
        Setting::setValue('general', 'maintenance_message', $data['maintenance_message'] ?? '', 'string');

        // Branding
        Setting::setValue('branding', 'site_logo', $data['site_logo'] ?? '', 'string');
        Setting::setValue('branding', 'site_logo_dark', $data['site_logo_dark'] ?? '', 'string');
        Setting::setValue('branding', 'site_favicon', $data['site_favicon'] ?? '', 'string');
        Setting::setValue('branding', 'primary_color', $data['primary_color'] ?? '#3b82f6', 'string');
        Setting::setValue('branding', 'secondary_color', $data['secondary_color'] ?? '#1e40af', 'string');

        // SMTP Settings
        Setting::setValue('mail', 'mailer', $data['mail_mailer'] ?? 'smtp', 'string');
        Setting::setValue('mail', 'host', $data['mail_host'] ?? '', 'string');
        Setting::setValue('mail', 'port', $data['mail_port'] ?? '587', 'string');
        Setting::setValue('mail', 'username', $data['mail_username'] ?? '', 'string');
        Setting::setValue('mail', 'password', $data['mail_password'] ?? '', 'string');
        Setting::setValue('mail', 'encryption', $data['mail_encryption'] ?? 'tls', 'string');
        Setting::setValue('mail', 'from_address', $data['mail_from_address'] ?? '', 'string');
        Setting::setValue('mail', 'from_name', $data['mail_from_name'] ?? '', 'string');

        // Banking Settings
        Setting::setValue('banking', 'bank_name', $data['bank_name'] ?? 'Finora Bank', 'string');
        Setting::setValue('banking', 'bank_code', $data['bank_code'] ?? '', 'string');
        Setting::setValue('banking', 'swift_code', $data['swift_code'] ?? '', 'string');
        Setting::setValue('banking', 'routing_number', $data['routing_number'] ?? '', 'string');
        Setting::setValue('banking', 'wire_transfer_enabled', $data['wire_transfer_enabled'] ?? true, 'boolean');
        Setting::setValue('banking', 'domestic_transfer_enabled', $data['domestic_transfer_enabled'] ?? true, 'boolean');
        Setting::setValue('banking', 'internal_transfer_enabled', $data['internal_transfer_enabled'] ?? true, 'boolean');

        // Wire Transfer Limits & Fees
        Setting::setValue('transfers', 'wire_daily_limit', $data['wire_daily_limit'] ?? '100000', 'string');
        Setting::setValue('transfers', 'wire_per_transaction_limit', $data['wire_per_transaction_limit'] ?? '50000', 'string');
        Setting::setValue('transfers', 'wire_flat_fee', $data['wire_flat_fee'] ?? '25', 'string');
        Setting::setValue('transfers', 'wire_percentage_fee', $data['wire_percentage_fee'] ?? '0.5', 'string');

        // Domestic Transfer Limits & Fees
        Setting::setValue('transfers', 'domestic_daily_limit', $data['domestic_daily_limit'] ?? '50000', 'string');
        Setting::setValue('transfers', 'domestic_per_transaction_limit', $data['domestic_per_transaction_limit'] ?? '25000', 'string');
        Setting::setValue('transfers', 'domestic_flat_fee', $data['domestic_flat_fee'] ?? '5', 'string');
        Setting::setValue('transfers', 'domestic_percentage_fee', $data['domestic_percentage_fee'] ?? '0', 'string');

        // Internal Transfer Limits & Fees
        Setting::setValue('transfers', 'internal_daily_limit', $data['internal_daily_limit'] ?? '100000', 'string');
        Setting::setValue('transfers', 'internal_per_transaction_limit', $data['internal_per_transaction_limit'] ?? '50000', 'string');
        Setting::setValue('transfers', 'internal_flat_fee', $data['internal_flat_fee'] ?? '0', 'string');
        Setting::setValue('transfers', 'internal_percentage_fee', $data['internal_percentage_fee'] ?? '0', 'string');

        // Card Fees
        Setting::setValue('fees', 'card_issuance_fee', $data['card_issuance_fee'] ?? '10', 'string');
        Setting::setValue('fees', 'card_replacement_fee', $data['card_replacement_fee'] ?? '15', 'string');

        // Security Settings
        Setting::setValue('security', 'two_factor_enabled', $data['two_factor_enabled'] ?? true, 'boolean');
        Setting::setValue('security', 'email_verification_required', $data['email_verification_required'] ?? true, 'boolean');
        Setting::setValue('security', 'kyc_required', $data['kyc_required'] ?? true, 'boolean');
        Setting::setValue('security', 'session_lifetime', $data['session_lifetime'] ?? '120', 'string');
        Setting::setValue('security', 'max_login_attempts', $data['max_login_attempts'] ?? '5', 'string');
        Setting::setValue('security', 'lockout_duration', $data['lockout_duration'] ?? '30', 'string');
        Setting::setValue('security', 'password_min_length', $data['password_min_length'] ?? '8', 'string');
        Setting::setValue('security', 'require_special_char', $data['require_special_char'] ?? true, 'boolean');
        Setting::setValue('security', 'require_uppercase', $data['require_uppercase'] ?? true, 'boolean');

        // Login Verification Settings
        Setting::setValue('security', 'login_require_email_otp', $data['login_require_email_otp'] ?? true, 'boolean');
        Setting::setValue('security', 'login_require_pin', $data['login_require_pin'] ?? true, 'boolean');

        // Email OTP Settings
        Setting::setValue('security', 'email_otp_enabled', $data['email_otp_enabled'] ?? false, 'boolean');
        Setting::setValue('security', 'email_otp_enforce_admin', $data['email_otp_enforce_admin'] ?? false, 'boolean');
        Setting::setValue('security', 'email_otp_enforce_user', $data['email_otp_enforce_user'] ?? false, 'boolean');
        Setting::setValue('security', 'email_otp_expiry', $data['email_otp_expiry'] ?? '10', 'string');
        Setting::setValue('security', 'email_otp_length', $data['email_otp_length'] ?? '6', 'string');

        // Transfer OTP Verification Settings
        Setting::setValue('security', 'transfer_otp_enabled', $data['transfer_otp_enabled'] ?? true, 'boolean');

        // ReCAPTCHA Settings
        Setting::setValue('security', 'recaptcha_enabled', $data['recaptcha_enabled'] ?? false, 'boolean');
        Setting::setValue('security', 'recaptcha_enforce_admin', $data['recaptcha_enforce_admin'] ?? false, 'boolean');
        Setting::setValue('security', 'recaptcha_enforce_user', $data['recaptcha_enforce_user'] ?? false, 'boolean');
        Setting::setValue('security', 'recaptcha_site_key', $data['recaptcha_site_key'] ?? '', 'string');
        Setting::setValue('security', 'recaptcha_secret_key', $data['recaptcha_secret_key'] ?? '', 'string');
        Setting::setValue('security', 'recaptcha_version', $data['recaptcha_version'] ?? 'v2', 'string');
        Setting::setValue('security', 'recaptcha_score_threshold', $data['recaptcha_score_threshold'] ?? '0.5', 'string');

        // Notification Settings
        Setting::setValue('notifications', 'email_notifications', $data['email_notifications'] ?? true, 'boolean');
        Setting::setValue('notifications', 'sms_notifications', $data['sms_notifications'] ?? false, 'boolean');
        Setting::setValue('notifications', 'push_notifications', $data['push_notifications'] ?? false, 'boolean');
        Setting::setValue('notifications', 'transaction_alerts', $data['transaction_alerts'] ?? true, 'boolean');
        Setting::setValue('notifications', 'login_alerts', $data['login_alerts'] ?? true, 'boolean');
        Setting::setValue('notifications', 'marketing_emails', $data['marketing_emails'] ?? false, 'boolean');

        // Deposit Settings
        Setting::setValue('deposits', 'check_deposit_enabled', $data['check_deposit_enabled'] ?? true, 'boolean');
        Setting::setValue('deposits', 'mobile_deposit_enabled', $data['mobile_deposit_enabled'] ?? true, 'boolean');
        Setting::setValue('deposits', 'crypto_deposit_enabled', $data['crypto_deposit_enabled'] ?? true, 'boolean');
        Setting::setValue('deposits', 'check_hold_period', $data['check_hold_period'] ?? '3', 'string');
        Setting::setValue('deposits', 'check_daily_limit', $data['check_daily_limit'] ?? '5000', 'string');
        Setting::setValue('deposits', 'check_per_transaction_limit', $data['check_per_transaction_limit'] ?? '2500', 'string');
        Setting::setValue('deposits', 'mobile_daily_limit', $data['mobile_daily_limit'] ?? '10000', 'string');
        Setting::setValue('deposits', 'mobile_per_transaction_limit', $data['mobile_per_transaction_limit'] ?? '5000', 'string');
        Setting::setValue('deposits', 'crypto_daily_limit', $data['crypto_daily_limit'] ?? '10000', 'string');
        Setting::setValue('deposits', 'crypto_per_transaction_limit', $data['crypto_per_transaction_limit'] ?? '5000', 'string');

        // Loan Settings
        Setting::setValue('loans', 'loans_enabled', $data['loans_enabled'] ?? true, 'boolean');
        Setting::setValue('loans', 'applications_enabled', $data['loan_applications_enabled'] ?? true, 'boolean');
        Setting::setValue('loans', 'auto_approve', $data['auto_approve_loans'] ?? false, 'boolean');
        Setting::setValue('loans', 'require_collateral', $data['require_collateral'] ?? false, 'boolean');
        Setting::setValue('loans', 'require_guarantor', $data['require_guarantor'] ?? false, 'boolean');
        Setting::setValue('loans', 'require_employment_verification', $data['require_employment_verification'] ?? true, 'boolean');
        Setting::setValue('loans', 'require_income_verification', $data['require_income_verification'] ?? true, 'boolean');
        Setting::setValue('loans', 'min_loan_amount', $data['min_loan_amount'] ?? '1000', 'string');
        Setting::setValue('loans', 'max_loan_amount', $data['max_loan_amount'] ?? '500000', 'string');
        Setting::setValue('loans', 'min_loan_term', $data['min_loan_term'] ?? '6', 'string');
        Setting::setValue('loans', 'max_loan_term', $data['max_loan_term'] ?? '360', 'string');
        Setting::setValue('loans', 'default_interest_rate', $data['default_interest_rate'] ?? '8.5', 'string');
        Setting::setValue('loans', 'max_active_loans', $data['max_active_loans'] ?? '3', 'string');
        Setting::setValue('loans', 'origination_fee', $data['loan_origination_fee'] ?? '1', 'string');
        Setting::setValue('loans', 'late_payment_fee', $data['late_payment_fee'] ?? '35', 'string');
        Setting::setValue('loans', 'grace_period_days', $data['grace_period_days'] ?? '15', 'string');
        Setting::setValue('loans', 'min_credit_score', $data['min_credit_score'] ?? '650', 'string');

        // Grant Settings
        Setting::setValue('grants', 'grants_enabled', $data['grants_enabled'] ?? true, 'boolean');
        Setting::setValue('grants', 'applications_enabled', $data['grant_applications_enabled'] ?? true, 'boolean');
        Setting::setValue('grants', 'auto_approve', $data['auto_approve_grants'] ?? false, 'boolean');
        Setting::setValue('grants', 'require_documents', $data['require_grant_documents'] ?? true, 'boolean');
        Setting::setValue('grants', 'require_eligibility_check', $data['require_eligibility_check'] ?? true, 'boolean');
        Setting::setValue('grants', 'max_applications_per_user', $data['max_grant_applications_per_user'] ?? '5', 'string');
        Setting::setValue('grants', 'application_cooldown_days', $data['grant_application_cooldown'] ?? '30', 'string');
        Setting::setValue('grants', 'notify_on_status_change', $data['notify_on_grant_status'] ?? true, 'boolean');
        Setting::setValue('grants', 'allow_reapplication', $data['allow_grant_reapplication'] ?? true, 'boolean');

        // Card Settings
        Setting::setValue('cards', 'virtual_cards_enabled', $data['virtual_cards_enabled'] ?? true, 'boolean');
        Setting::setValue('cards', 'physical_cards_enabled', $data['physical_cards_enabled'] ?? true, 'boolean');
        Setting::setValue('cards', 'card_daily_limit', $data['card_daily_limit'] ?? '5000', 'string');
        Setting::setValue('cards', 'card_monthly_limit', $data['card_monthly_limit'] ?? '50000', 'string');

        // SEO Settings
        Setting::setValue('seo', 'meta_title', $data['seo_meta_title'] ?? '', 'string');
        Setting::setValue('seo', 'meta_description', $data['seo_meta_description'] ?? '', 'string');
        Setting::setValue('seo', 'meta_keywords', $data['seo_meta_keywords'] ?? '', 'string');
        Setting::setValue('seo', 'og_title', $data['seo_og_title'] ?? '', 'string');
        Setting::setValue('seo', 'og_description', $data['seo_og_description'] ?? '', 'string');
        Setting::setValue('seo', 'og_image', $data['seo_og_image'] ?? '', 'string');
        Setting::setValue('seo', 'twitter_card', $data['seo_twitter_card'] ?? 'summary_large_image', 'string');
        Setting::setValue('seo', 'twitter_site', $data['seo_twitter_site'] ?? '', 'string');
        Setting::setValue('seo', 'google_analytics', $data['seo_google_analytics'] ?? '', 'string');
        Setting::setValue('seo', 'google_tag_manager', $data['seo_google_tag_manager'] ?? '', 'string');
        Setting::setValue('seo', 'facebook_pixel', $data['seo_facebook_pixel'] ?? '', 'string');
        Setting::setValue('seo', 'robots_txt', $data['seo_robots_txt'] ?? '', 'string');
        Setting::setValue('seo', 'custom_head_code', $data['seo_custom_head_code'] ?? '', 'string');

        // Maintenance Settings
        Setting::setValue('general', 'maintenance_allowed_ips', $data['maintenance_allowed_ips'] ?? '', 'string');
        Setting::setValue('general', 'maintenance_secret', $data['maintenance_secret'] ?? '', 'string');

        // Feature Permissions - User Dashboard Access Controls
        // Account Features
        Setting::setValue('permissions', 'bank_accounts', $data['perm_bank_accounts'] ?? true, 'boolean');
        Setting::setValue('permissions', 'beneficiaries', $data['perm_beneficiaries'] ?? true, 'boolean');
        Setting::setValue('permissions', 'statements', $data['perm_statements'] ?? true, 'boolean');

        // Transfer Features
        Setting::setValue('permissions', 'wire_transfers', $data['perm_wire_transfers'] ?? true, 'boolean');
        Setting::setValue('permissions', 'internal_transfers', $data['perm_internal_transfers'] ?? true, 'boolean');
        Setting::setValue('permissions', 'domestic_transfers', $data['perm_domestic_transfers'] ?? true, 'boolean');

        // Deposit Features
        Setting::setValue('permissions', 'check_deposits', $data['perm_check_deposits'] ?? true, 'boolean');
        Setting::setValue('permissions', 'mobile_deposits', $data['perm_mobile_deposits'] ?? true, 'boolean');
        Setting::setValue('permissions', 'crypto_deposits', $data['perm_crypto_deposits'] ?? true, 'boolean');

        // Loans & Grants Features
        Setting::setValue('permissions', 'loans', $data['perm_loans'] ?? true, 'boolean');
        Setting::setValue('permissions', 'loan_applications', $data['perm_loan_applications'] ?? true, 'boolean');
        Setting::setValue('permissions', 'grants', $data['perm_grants'] ?? true, 'boolean');
        Setting::setValue('permissions', 'grant_applications', $data['perm_grant_applications'] ?? true, 'boolean');
        Setting::setValue('permissions', 'tax_refunds', $data['perm_tax_refunds'] ?? true, 'boolean');

        // Card Features
        Setting::setValue('permissions', 'cards', $data['perm_cards'] ?? true, 'boolean');
        Setting::setValue('permissions', 'virtual_cards', $data['perm_virtual_cards'] ?? true, 'boolean');
        Setting::setValue('permissions', 'physical_cards', $data['perm_physical_cards'] ?? true, 'boolean');

        // Other Financial Features
        Setting::setValue('permissions', 'withdrawals', $data['perm_withdrawals'] ?? true, 'boolean');
        Setting::setValue('permissions', 'money_requests', $data['perm_money_requests'] ?? true, 'boolean');
        Setting::setValue('permissions', 'exchange_money', $data['perm_exchange_money'] ?? true, 'boolean');
        Setting::setValue('permissions', 'vouchers', $data['perm_vouchers'] ?? true, 'boolean');
        Setting::setValue('permissions', 'rewards', $data['perm_rewards'] ?? true, 'boolean');

        // Transaction & History
        Setting::setValue('permissions', 'transaction_history', $data['perm_transaction_history'] ?? true, 'boolean');

        // Support
        Setting::setValue('permissions', 'support_tickets', $data['perm_support_tickets'] ?? true, 'boolean');

        Notification::make()
            ->title('Settings Saved')
            ->body('All settings have been saved successfully!')
            ->success()
            ->send();
    }

    /**
     * Clear application cache.
     */
    public function clearAppCache(): void
    {
        try {
            \Artisan::call('cache:clear');

            Notification::make()
                ->title('Cache Cleared')
                ->body('Application cache has been cleared successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to clear cache: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Clear view cache.
     */
    public function clearViewCache(): void
    {
        try {
            \Artisan::call('view:clear');

            Notification::make()
                ->title('View Cache Cleared')
                ->body('Compiled view files have been cleared successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to clear view cache: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Clear route cache.
     */
    public function clearRouteCache(): void
    {
        try {
            \Artisan::call('route:clear');

            Notification::make()
                ->title('Route Cache Cleared')
                ->body('Route cache has been cleared successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to clear route cache: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Clear config cache.
     */
    public function clearConfigCache(): void
    {
        try {
            \Artisan::call('config:clear');

            Notification::make()
                ->title('Config Cache Cleared')
                ->body('Configuration cache has been cleared successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to clear config cache: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Optimize application.
     */
    public function optimizeApp(): void
    {
        try {
            \Artisan::call('optimize');

            Notification::make()
                ->title('Application Optimized')
                ->body('Application has been optimized for better performance.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to optimize application: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Clear all caches.
     */
    public function clearAllCache(): void
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            \Artisan::call('config:clear');
            \Artisan::call('event:clear');

            Notification::make()
                ->title('All Caches Cleared')
                ->body('All application caches have been cleared successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to clear caches: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}

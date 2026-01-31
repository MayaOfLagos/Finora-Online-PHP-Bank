<?php

namespace App\Filament\Pages;

use App\Models\ReferralSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class ManageReferralSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Referrals';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Referral Settings';

    protected static ?string $slug = 'referral-settings';

    protected static ?string $navigationLabel = 'Settings';

    protected string $view = 'filament.pages.manage-referral-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        // Initialize default settings if not exists
        ReferralSetting::initializeDefaults();

        $this->form->fill($this->loadSettings());
    }

    protected function loadSettings(): array
    {
        return [
            // Program Settings
            'referral_enabled' => ReferralSetting::isEnabled('referral_enabled'),

            // New User Bonus Settings
            'new_user_bonus_enabled' => ReferralSetting::isEnabled('new_user_bonus_enabled'),
            'new_user_bonus_amount' => ReferralSetting::get('new_user_bonus_amount', 500),

            // Base Settings
            'base_reward_amount' => ReferralSetting::get('base_reward_amount', 5000),

            // Timing Settings
            'reward_delay_hours' => ReferralSetting::get('reward_delay_hours', 0),

            // Requirements
            'min_deposit_for_reward' => ReferralSetting::get('min_deposit_for_reward', 0),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Program Status')
                    ->description('Control the overall referral program')
                    ->icon('heroicon-o-power')
                    ->schema([
                        Toggle::make('referral_enabled')
                            ->label('Enable Referral Program')
                            ->helperText('When disabled, users cannot use referral codes to sign up and no rewards will be distributed.')
                            ->live()
                            ->afterStateUpdated(fn () => $this->saveSettings()),
                    ])
                    ->collapsible(),

                Section::make('New User Welcome Bonus')
                    ->description('Configure rewards for new users who sign up via referral')
                    ->icon('heroicon-o-gift')
                    ->schema([
                        Toggle::make('new_user_bonus_enabled')
                            ->label('Enable Welcome Bonus for New Users')
                            ->helperText('When enabled, new users who sign up with a referral code will receive a welcome bonus. Inviters always earn their level-based reward regardless of this setting.')
                            ->live()
                            ->afterStateUpdated(fn () => $this->saveSettings()),

                        TextInput::make('new_user_bonus_amount')
                            ->label('Welcome Bonus Amount')
                            ->helperText('Fixed amount in cents (e.g., 500 = $5.00)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('¢')
                            ->suffix(fn ($state) => '= $'.number_format(($state ?? 0) / 100, 2))
                            ->visible(fn (Get $get) => $get('new_user_bonus_enabled'))
                            ->afterStateUpdated(fn () => $this->saveSettings()),
                    ])
                    ->collapsible(),

                Section::make('Reward Calculations')
                    ->description('Settings for percentage-based reward calculations')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        TextInput::make('base_reward_amount')
                            ->label('Base Reward Amount')
                            ->helperText('Base amount in cents used when levels have percentage-based rewards (e.g., 5000 = $50.00 base)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('¢')
                            ->suffix(fn ($state) => '= $'.number_format(($state ?? 0) / 100, 2))
                            ->afterStateUpdated(fn () => $this->saveSettings()),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Timing & Delays')
                    ->description('Configure when rewards become available')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextInput::make('reward_delay_hours')
                            ->label('Reward Delay')
                            ->helperText('Hours before rewards become claimable. Set to 0 for instant rewards.')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('hours')
                            ->afterStateUpdated(fn () => $this->saveSettings()),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Requirements')
                    ->description('Optional requirements before rewards can be claimed')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('min_deposit_for_reward')
                            ->label('Minimum Deposit Required')
                            ->helperText('Minimum deposit amount (in cents) required before rewards can be claimed. Set to 0 for no requirement.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('¢')
                            ->suffix(fn ($state) => $state > 0 ? '= $'.number_format(($state ?? 0) / 100, 2) : 'No requirement')
                            ->afterStateUpdated(fn () => $this->saveSettings()),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        // Program Settings
        ReferralSetting::set('referral_enabled', $data['referral_enabled'], 'boolean', 'program', 'Enable or disable the referral program');

        // New User Bonus Settings
        ReferralSetting::set('new_user_bonus_enabled', $data['new_user_bonus_enabled'], 'boolean', 'rewards', 'Enable welcome bonus for new users');
        ReferralSetting::set('new_user_bonus_amount', $data['new_user_bonus_amount'], 'integer', 'rewards', 'Welcome bonus amount in cents');

        // Base Settings
        ReferralSetting::set('base_reward_amount', $data['base_reward_amount'], 'integer', 'rewards', 'Base amount for percentage calculations');

        // Timing Settings
        ReferralSetting::set('reward_delay_hours', $data['reward_delay_hours'], 'integer', 'timing', 'Hours before rewards become claimable');

        // Requirements
        ReferralSetting::set('min_deposit_for_reward', $data['min_deposit_for_reward'], 'integer', 'requirements', 'Minimum deposit before claiming rewards');

        // Clear all settings cache
        ReferralSetting::clearCache();

        Notification::make()
            ->title('Settings Saved')
            ->body('Referral settings have been updated successfully.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_defaults')
                ->label('Reset to Defaults')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Settings')
                ->modalDescription('This will reset all referral settings to their default values. Are you sure?')
                ->modalWidth(Width::Medium)
                ->action(function () {
                    // Delete all settings
                    ReferralSetting::truncate();

                    // Re-initialize defaults
                    ReferralSetting::initializeDefaults();

                    // Clear cache
                    ReferralSetting::clearCache();

                    // Reload form
                    $this->form->fill($this->loadSettings());

                    Notification::make()
                        ->title('Settings Reset')
                        ->body('All settings have been reset to default values.')
                        ->success()
                        ->send();
                }),

            Action::make('save')
                ->label('Save Settings')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->action(fn () => $this->saveSettings()),
        ];
    }
}

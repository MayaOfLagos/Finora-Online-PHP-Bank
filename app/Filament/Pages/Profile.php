<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Custom Profile page extending Filament's EditProfile.
 *
 * Note: MFA (Multi-Factor Authentication) is automatically handled by Filament
 * when multiFactorAuthentication() is configured in the panel provider.
 * Filament will add MFA setup UI to the profile page automatically.
 *
 * @property-read Schema $form
 */
class Profile extends EditProfile
{
    protected static ?string $title = 'My Profile';

    protected static ?string $slug = 'profile';

    protected string $view = 'filament.pages.profile';

    /**
     * Set maximum content width to full width.
     */
    protected Width|string|null $maxContentWidth = Width::ThreeExtraLarge;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getLabel(): string
    {
        return __('Profile');
    }

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'profile_photo_path' => $user->profile_photo_path,

            // Password fields are empty
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Profile')
                    ->persistTabInQueryString()
                    ->contained(false)
                    ->schema([
                        Tab::make('Profile')
                            ->icon('heroicon-o-user')
                            ->schema($this->getProfileSchema()),

                        Tab::make('Password')
                            ->icon('heroicon-o-key')
                            ->schema($this->getPasswordSchema()),

                        Tab::make('Security')
                            ->icon('heroicon-o-shield-check')
                            ->schema($this->getSecuritySchema()),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Override content() to include both the form and MFA component.
     * This ensures MFA is rendered within the proper Filament context.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                ...Arr::wrap($this->getMultiFactorAuthenticationContentComponent()),
            ]);
    }

    /**
     * Override to wrap form in proper Form component with submit handler.
     */
    public function getFormContentComponent(): Form
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('saveProfile')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions()),
            ]);
    }

    protected function getProfileSchema(): array
    {
        return [
            Section::make('Personal Information')
                ->description('Update your personal information and profile photo.')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('first_name')
                                ->label('First Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('last_name')
                                ->label('Last Name')
                                ->required()
                                ->maxLength(255),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->disabled()
                                ->helperText('Email cannot be changed.'),

                            TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(20),
                        ]),

                    FileUpload::make('profile_photo_path')
                        ->label('Profile Photo')
                        ->image()
                        ->avatar()
                        ->imageEditor()
                        ->directory('profile-photos')
                        ->disk('public')
                        ->maxSize(2048)
                        ->helperText('Upload a square image. Max 2MB.'),
                ]),
        ];
    }

    protected function getPasswordSchema(): array
    {
        return [
            Section::make('Change Password')
                ->description('Ensure your account is using a long, random password to stay secure.')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->revealable()
                        ->autocomplete('current-password')
                        ->required(fn ($get) => filled($get('new_password'))),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('new_password')
                                ->label('New Password')
                                ->password()
                                ->revealable()
                                ->autocomplete('new-password')
                                ->rule(Password::min(8)->mixedCase()->numbers())
                                ->helperText('Min 8 characters with mixed case and numbers.'),

                            TextInput::make('new_password_confirmation')
                                ->label('Confirm New Password')
                                ->password()
                                ->revealable()
                                ->autocomplete('new-password')
                                ->same('new_password'),
                        ]),
                ]),
        ];
    }

    protected function getSecuritySchema(): array
    {
        $user = Auth::user();

        // Check Filament MFA status
        $hasAppAuth = filled($user->getAppAuthenticationSecret());
        $hasEmailAuth = $user->hasEmailAuthentication();
        $hasMfa = $hasAppAuth || $hasEmailAuth;

        return [
            Section::make('Security Overview')
                ->description('Review your account security settings and recent activity.')
                ->icon('heroicon-o-shield-exclamation')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Section::make('Multi-Factor Authentication')
                                ->description($hasMfa ? 'Enabled' : 'Not Configured')
                                ->icon($hasMfa ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                ->iconColor($hasMfa ? 'success' : 'danger')
                                ->compact(),

                            Section::make('Email Verified')
                                ->description($user->hasVerifiedEmail() ? 'Verified' : 'Not Verified')
                                ->icon($user->hasVerifiedEmail() ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                ->iconColor($user->hasVerifiedEmail() ? 'success' : 'warning')
                                ->compact(),

                            Section::make('Account Status')
                                ->description($user->is_active ? 'Active' : 'Suspended')
                                ->icon($user->is_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                ->iconColor($user->is_active ? 'success' : 'danger')
                                ->compact(),
                        ]),
                ]),

            // Note: MFA management is rendered separately in the blade view
            // to avoid form nesting issues with modal submissions

            Section::make('Session Management')
                ->description('View and manage your active sessions.')
                ->icon('heroicon-o-computer-desktop')
                ->schema([
                    \Filament\Schemas\Components\View::make('filament.pages.profile.sessions')
                        ->viewData([
                            'sessions' => $this->getSessions(),
                        ]),
                ]),

            Section::make('Recent Login Activity')
                ->description('Your last 5 login attempts.')
                ->icon('heroicon-o-clock')
                ->schema([
                    \Filament\Schemas\Components\View::make('filament.pages.profile.login-history')
                        ->viewData([
                            'loginHistory' => $this->getLoginHistory(),
                        ]),
                ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_profile')
                ->label('Save Changes')
                ->icon('heroicon-o-check')
                ->action('saveProfile'),
        ];
    }

    public function saveProfile(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // Update profile info
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->phone_number = $data['phone_number'];

        // Update profile photo if changed
        if (isset($data['profile_photo_path'])) {
            $user->profile_photo_path = $data['profile_photo_path'];
        }

        // Update password if provided
        if (filled($data['new_password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Error')
                    ->body('Current password is incorrect.')
                    ->danger()
                    ->send();

                return;
            }

            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        // Clear password fields
        $this->form->fill([
            ...$data,
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);

        Notification::make()
            ->title('Profile Updated')
            ->body('Your profile has been updated successfully.')
            ->success()
            ->send();
    }

    protected function getSessions(): array
    {
        // Get current sessions from database (if using database sessions)
        // For now, return placeholder data
        return [
            [
                'device' => 'Current Session',
                'ip' => request()->ip(),
                'last_active' => now()->format('M d, Y H:i'),
                'is_current' => true,
            ],
        ];
    }

    protected function getLoginHistory(): array
    {
        $user = Auth::user();

        return $user->loginHistories()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($login) => [
                'ip' => $login->ip_address,
                'device' => $login->device_type ?? 'Unknown',
                'browser' => $login->browser ?? 'Unknown',
                'location' => $login->location ?? 'Unknown',
                'date' => $login->created_at->format('M d, Y H:i'),
                'status' => $login->status ?? 'success',
            ])
            ->toArray();
    }

    public function logoutOtherSessions(): void
    {
        Auth::logoutOtherDevices(request()->input('password', ''));

        Notification::make()
            ->title('Sessions Terminated')
            ->body('All other browser sessions have been logged out.')
            ->success()
            ->send();
    }
}

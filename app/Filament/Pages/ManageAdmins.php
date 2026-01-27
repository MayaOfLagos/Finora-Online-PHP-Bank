<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Split;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class ManageAdmins extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected string $view = 'filament.pages.manage-admins';

    protected static ?string $navigationLabel = 'All Admins';

    protected static ?string $title = 'Admin Management';

    protected static ?string $slug = 'admins';

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 0;

    public static function getNavigationBadge(): ?string
    {
        return (string) User::whereIn('role', ['staff', 'admin', 'super_admin'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage administrators and staff with access to the admin panel';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_admin')
                ->label('Add New Admin')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalWidth(Width::ExtraLarge)
                ->modalHeading('Create New Administrator')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255),
                    ]),
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email')
                        ->maxLength(255),
                    Grid::make(2)->schema([
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20),
                        Select::make('role')
                            ->label('Role')
                            ->required()
                            ->options(function () {
                                $currentUser = auth()->user();
                                if ($currentUser->isSuperAdmin()) {
                                    return [
                                        'staff' => 'Staff',
                                        'admin' => 'Admin',
                                        'super_admin' => 'Super Admin',
                                    ];
                                }
                                return [
                                    'staff' => 'Staff',
                                    'admin' => 'Admin',
                                ];
                            })
                            ->default('staff'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->revealable(),
                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required()
                            ->same('password')
                            ->revealable(),
                    ]),
                ])
                ->action(function (array $data) {
                    $admin = User::create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'phone_number' => $data['phone_number'] ?? null,
                        'password' => Hash::make($data['password']),
                        'role' => $data['role'],
                        'is_active' => true,
                        'is_verified' => true,
                        'email_verified_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Admin Created')
                        ->body("Administrator {$admin->full_name} has been created successfully.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereIn('role', ['staff', 'admin', 'super_admin'])
                    ->orderByRaw("CASE role WHEN 'super_admin' THEN 1 WHEN 'admin' THEN 2 WHEN 'staff' THEN 3 END")
                    ->latest()
            )
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->description(fn ($record) => $record->email)
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(['first_name']),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'Unknown')
                    ->color(fn ($state) => $state?->color() ?? 'gray')
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'staff' => 'Staff',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    TableAction::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn ($record) => "Admin: {$record->full_name}")
                        ->modalWidth(Width::FourExtraLarge)
                        ->modalContent(fn ($record) => view('filament.pages.admin-details', ['admin' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    TableAction::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalHeading(fn ($record) => "Edit: {$record->full_name}")
                        ->modalWidth(Width::ExtraLarge)
                        ->fillForm(fn ($record) => [
                            'first_name' => $record->first_name,
                            'last_name' => $record->last_name,
                            'email' => $record->email,
                            'phone_number' => $record->phone_number,
                            'role' => $record->role->value,
                            'is_active' => $record->is_active,
                        ])
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('first_name')
                                    ->label('First Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Grid::make(2)->schema([
                                TextInput::make('phone_number')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(20),
                                Select::make('role')
                                    ->label('Role')
                                    ->required()
                                    ->options(function () {
                                        $currentUser = auth()->user();
                                        if ($currentUser->isSuperAdmin()) {
                                            return [
                                                'staff' => 'Staff',
                                                'admin' => 'Admin',
                                                'super_admin' => 'Super Admin',
                                            ];
                                        }
                                        return [
                                            'staff' => 'Staff',
                                            'admin' => 'Admin',
                                        ];
                                    })
                                    ->disabled(fn ($record) => $this->isLastSuperAdmin($record)),
                            ]),
                            Select::make('is_active')
                                ->label('Status')
                                ->options([
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ])
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            // Check if trying to change the last super admin's role
                            if ($this->isLastSuperAdmin($record) && $data['role'] !== 'super_admin') {
                                Notification::make()
                                    ->title('Cannot Change Role')
                                    ->body('This is the last Super Admin. At least one Super Admin must exist.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Non-super admins cannot assign super_admin role
                            if (!auth()->user()->isSuperAdmin() && $data['role'] === 'super_admin') {
                                Notification::make()
                                    ->title('Permission Denied')
                                    ->body('Only Super Admins can assign the Super Admin role.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $record->update([
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'],
                                'email' => $data['email'],
                                'phone_number' => $data['phone_number'],
                                'role' => $data['role'],
                                'is_active' => (bool) $data['is_active'],
                            ]);

                            Notification::make()
                                ->title('Admin Updated')
                                ->body("Administrator {$record->full_name} has been updated successfully.")
                                ->success()
                                ->send();
                        }),

                    TableAction::make('change_password')
                        ->label('Change Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->modalHeading(fn ($record) => "Change Password: {$record->full_name}")
                        ->modalWidth(Width::Medium)
                        ->schema([
                            TextInput::make('password')
                                ->label('New Password')
                                ->password()
                                ->required()
                                ->rule(Password::default())
                                ->revealable(),
                            TextInput::make('password_confirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->required()
                                ->same('password')
                                ->revealable(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'password' => Hash::make($data['password']),
                            ]);

                            Notification::make()
                                ->title('Password Changed')
                                ->body("Password for {$record->full_name} has been changed successfully.")
                                ->success()
                                ->send();
                        }),

                    TableAction::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => $record->is_active ? 'Deactivate Admin' : 'Activate Admin')
                        ->modalDescription(fn ($record) => $record->is_active
                            ? "Are you sure you want to deactivate {$record->full_name}? They will no longer be able to access the admin panel."
                            : "Are you sure you want to activate {$record->full_name}? They will regain access to the admin panel."
                        )
                        ->action(function ($record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->title($record->is_active ? 'Admin Activated' : 'Admin Deactivated')
                                ->body("{$record->full_name} has been " . ($record->is_active ? 'activated' : 'deactivated') . ".")
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->id !== auth()->id()), // Can't deactivate yourself

                    TableAction::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Administrator')
                        ->modalDescription(fn ($record) => "Are you sure you want to delete {$record->full_name}? This action cannot be undone.")
                        ->action(function ($record) {
                            // Can't delete yourself
                            if ($record->id === auth()->id()) {
                                Notification::make()
                                    ->title('Cannot Delete')
                                    ->body('You cannot delete your own account.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Only super admins can delete other super admins
                            if ($record->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
                                Notification::make()
                                    ->title('Permission Denied')
                                    ->body('Only Super Admins can delete other Super Admins.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Can't delete the last super admin
                            if ($this->isLastSuperAdmin($record)) {
                                Notification::make()
                                    ->title('Cannot Delete')
                                    ->body('This is the last Super Admin. At least one Super Admin must exist.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $name = $record->full_name;
                            $record->delete();

                            Notification::make()
                                ->title('Admin Deleted')
                                ->body("Administrator {$name} has been deleted.")
                                ->success()
                                ->send();
                        })
                        ->visible(function ($record) {
                            // Can't delete yourself
                            if ($record->id === auth()->id()) {
                                return false;
                            }

                            // Only super admins can delete other super admins
                            if ($record->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
                                return false;
                            }

                            return true;
                        }),
                ])->tooltip('Actions'),
            ])
            ->emptyStateHeading('No Administrators')
            ->emptyStateDescription('Create your first administrator to get started.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->poll('30s');
    }

    /**
     * Check if the given user is the last super admin.
     */
    protected function isLastSuperAdmin(User $user): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return User::where('role', 'super_admin')->count() === 1;
    }
}

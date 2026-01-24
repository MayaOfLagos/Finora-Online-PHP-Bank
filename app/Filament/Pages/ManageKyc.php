<?php

namespace App\Filament\Pages;

use App\Enums\KycStatus;
use App\Models\KycVerification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use UnitEnum;

class ManageKyc extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected string $view = 'filament.pages.manage-kyc';

    protected static ?string $navigationLabel = 'KYC Verifications';

    protected static ?string $title = 'KYC Verifications';

    protected static ?string $slug = 'kyc';

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) KycVerification::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Review and manage user KYC verification requests';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manage_templates')
                ->label('Manage Document Templates')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn () => route('filament.admin.pages.kyc.template')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(KycVerification::query()->with(['user', 'template', 'verifier']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                ImageColumn::make('user.profile_photo_path')
                    ->label('User')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->user?->full_name ?? 'U').'&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                TextColumn::make('user.full_name')
                    ->label('Customer')
                    ->description(fn ($record) => $record->user?->email)
                    ->searchable(['users.first_name', 'users.last_name', 'users.email'])
                    ->sortable(),

                TextColumn::make('document_type_name')
                    ->label('Document Type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('document_number')
                    ->label('Document #')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                ImageColumn::make('document_front_path')
                    ->label('Front')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->extraImgAttributes(['class' => 'rounded cursor-pointer hover:opacity-75'])
                    ->action(
                        TableAction::make('preview_front')
                            ->modalHeading('Document Front')
                            ->modalWidth(Width::FourExtraLarge)
                            ->modalContent(fn ($record) => new HtmlString(
                                '<div class="flex justify-center p-4"><img src="'.asset('storage/'.$record->document_front_path).'" class="max-w-full max-h-[70vh] rounded-lg shadow-lg" alt="Document Front"></div>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                    )
                    ->placeholder('—'),

                ImageColumn::make('document_back_path')
                    ->label('Back')
                    ->disk('public')
                    ->size(50)
                    ->square()
                    ->extraImgAttributes(['class' => 'rounded cursor-pointer hover:opacity-75'])
                    ->action(
                        TableAction::make('preview_back')
                            ->modalHeading('Document Back')
                            ->modalWidth(Width::FourExtraLarge)
                            ->modalContent(fn ($record) => new HtmlString(
                                '<div class="flex justify-center p-4"><img src="'.asset('storage/'.$record->document_back_path).'" class="max-w-full max-h-[70vh] rounded-lg shadow-lg" alt="Document Back"></div>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                    )
                    ->placeholder('—')
                    ->toggleable(),

                ImageColumn::make('selfie_path')
                    ->label('Selfie')
                    ->disk('public')
                    ->size(50)
                    ->circular()
                    ->extraImgAttributes(['class' => 'cursor-pointer hover:opacity-75'])
                    ->action(
                        TableAction::make('preview_selfie')
                            ->modalHeading('Selfie with Document')
                            ->modalWidth(Width::FourExtraLarge)
                            ->modalContent(fn ($record) => new HtmlString(
                                '<div class="flex justify-center p-4"><img src="'.asset('storage/'.$record->selfie_path).'" class="max-w-full max-h-[70vh] rounded-lg shadow-lg" alt="Selfie"></div>'
                            ))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                    )
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->sortable(),

                TextColumn::make('verifier.full_name')
                    ->label('Reviewed By')
                    ->placeholder('Pending')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('verified_at')
                    ->label('Reviewed At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y \a\t g:i A')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(KycStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]))
                    ->label('Status'),

                SelectFilter::make('template_id')
                    ->relationship('template', 'name')
                    ->label('Document Type')
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    TableAction::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn ($record) => 'KYC Verification #'.$record->id)
                        ->modalWidth(Width::FiveExtraLarge)
                        ->modalContent(fn ($record) => view('filament.pages.kyc-details', ['record' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    TableAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve KYC Verification')
                        ->modalDescription(fn ($record) => "Are you sure you want to approve the KYC verification for {$record->user?->full_name}? They will receive an email notification.")
                        ->modalWidth(Width::Medium)
                        ->form([
                            Textarea::make('admin_notes')
                                ->label('Admin Notes (Optional)')
                                ->rows(2)
                                ->placeholder('Add any internal notes about this approval...'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->approve(auth()->id(), $data['admin_notes'] ?? null);

                            Notification::make()
                                ->title('KYC Approved')
                                ->body("KYC verification for {$record->user?->full_name} has been approved.")
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === KycStatus::Pending),

                    TableAction::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject KYC Verification')
                        ->modalDescription(fn ($record) => "Reject the KYC verification for {$record->user?->full_name}? They will receive an email notification with the reason.")
                        ->modalWidth(Width::Medium)
                        ->form([
                            Select::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->options([
                                    'Document is blurry or unreadable' => 'Document is blurry or unreadable',
                                    'Document has expired' => 'Document has expired',
                                    'Document does not match account information' => 'Document does not match account information',
                                    'Selfie does not match document photo' => 'Selfie does not match document photo',
                                    'Document appears to be altered or fake' => 'Document appears to be altered or fake',
                                    'Required information is missing' => 'Required information is missing',
                                    'Poor image quality' => 'Poor image quality',
                                    'Wrong document type submitted' => 'Wrong document type submitted',
                                    'other' => 'Other (specify below)',
                                ])
                                ->live(),
                            Textarea::make('custom_reason')
                                ->label('Custom Reason')
                                ->required()
                                ->visible(fn ($get) => $get('rejection_reason') === 'other')
                                ->rows(2),
                            Textarea::make('admin_notes')
                                ->label('Admin Notes (Internal)')
                                ->rows(2)
                                ->placeholder('Add any internal notes...'),
                        ])
                        ->action(function ($record, array $data) {
                            $reason = $data['rejection_reason'] === 'other'
                                ? $data['custom_reason']
                                : $data['rejection_reason'];

                            $record->reject(auth()->id(), $reason, $data['admin_notes'] ?? null);

                            Notification::make()
                                ->title('KYC Rejected')
                                ->body("KYC verification for {$record->user?->full_name} has been rejected.")
                                ->warning()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === KycStatus::Pending),

                    TableAction::make('request_resubmission')
                        ->label('Request Resubmission')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->modalHeading('Request Resubmission')
                        ->modalWidth(Width::Medium)
                        ->form([
                            Textarea::make('message')
                                ->label('Message to User')
                                ->required()
                                ->rows(3)
                                ->placeholder('Explain what needs to be corrected...'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->reject(auth()->id(), $data['message'], 'Resubmission requested');

                            Notification::make()
                                ->title('Resubmission Requested')
                                ->body("A resubmission request has been sent to {$record->user?->full_name}.")
                                ->info()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->status === KycStatus::Pending),

                    TableAction::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('gray')
                        ->modalHeading('Edit KYC Verification')
                        ->modalWidth(Width::Large)
                        ->fillForm(fn ($record) => [
                            'document_number' => $record->document_number,
                            'admin_notes' => $record->admin_notes,
                            'rejection_reason' => $record->rejection_reason,
                        ])
                        ->form([
                            TextInput::make('document_number')
                                ->label('Document Number'),
                            Textarea::make('admin_notes')
                                ->label('Admin Notes')
                                ->rows(2),
                            Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->rows(2),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update($data);

                            Notification::make()
                                ->title('KYC Updated')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete KYC Verification')
                        ->modalDescription('Are you sure you want to delete this KYC verification? This action cannot be undone.'),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No KYC verifications')
            ->emptyStateDescription('When users submit KYC verification requests, they will appear here.')
            ->emptyStateIcon('heroicon-o-identification')
            ->poll('30s');
    }
}

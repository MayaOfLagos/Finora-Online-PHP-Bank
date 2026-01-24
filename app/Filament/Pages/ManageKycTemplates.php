<?php

namespace App\Filament\Pages;

use App\Models\KycDocumentTemplate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Action as TableAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use UnitEnum;

class ManageKycTemplates extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.pages.manage-kyc-templates';

    protected static ?string $navigationLabel = 'KYC Templates';

    protected static ?string $title = 'KYC Document Templates';

    protected static ?string $slug = 'kyc/template';

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = true;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Define the types of documents users can submit for KYC verification';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_kyc')
                ->label('Back to KYC List')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.pages.kyc')),

            Action::make('create_template')
                ->label('Create Template')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Create Document Template')
                ->modalWidth(Width::ExtraLarge)
                ->form($this->getTemplateForm())
                ->action(function (array $data) {
                    KycDocumentTemplate::create([
                        'name' => $data['name'],
                        'slug' => Str::slug($data['name']),
                        'description' => $data['description'],
                        'instructions' => $data['instructions'],
                        'is_required' => $data['is_required'],
                        'requires_front_image' => $data['requires_front_image'],
                        'requires_back_image' => $data['requires_back_image'],
                        'requires_selfie' => $data['requires_selfie'],
                        'requires_document_number' => $data['requires_document_number'],
                        'accepted_formats' => $data['accepted_formats'],
                        'max_file_size' => $data['max_file_size'],
                        'sort_order' => $data['sort_order'],
                        'is_active' => $data['is_active'],
                    ]);

                    Notification::make()
                        ->title('Template Created')
                        ->body("Document template '{$data['name']}' has been created successfully.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getTemplateForm(): array
    {
        return [
            Section::make('Basic Information')
                ->description('Define the document type name and description')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Passport, National ID Card')
                            ->helperText('The name users will see when selecting this document type'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ]),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->placeholder('Brief description of this document type...')
                        ->helperText('Displayed to users on the document selection page'),
                    Textarea::make('instructions')
                        ->label('Submission Instructions')
                        ->rows(3)
                        ->placeholder('Instructions for users when uploading this document...')
                        ->helperText('Detailed instructions shown when users are uploading documents'),
                ]),

            Section::make('Requirements')
                ->description('Specify what users need to provide')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_required')
                            ->label('Required Document')
                            ->helperText('User must submit this document for KYC approval')
                            ->default(true),
                        Toggle::make('requires_document_number')
                            ->label('Require Document Number')
                            ->helperText('User must enter the document ID/number')
                            ->default(true),
                    ]),
                    Grid::make(3)->schema([
                        Toggle::make('requires_front_image')
                            ->label('Front Image Required')
                            ->helperText('Front side of document')
                            ->default(true),
                        Toggle::make('requires_back_image')
                            ->label('Back Image Required')
                            ->helperText('Back side of document')
                            ->default(false),
                        Toggle::make('requires_selfie')
                            ->label('Selfie Required')
                            ->helperText('Selfie holding the document')
                            ->default(false),
                    ]),
                ]),

            Section::make('File Settings')
                ->description('Configure upload restrictions')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('accepted_formats')
                            ->label('Accepted File Formats')
                            ->multiple()
                            ->options([
                                'jpg' => 'JPEG (.jpg)',
                                'jpeg' => 'JPEG (.jpeg)',
                                'png' => 'PNG (.png)',
                                'pdf' => 'PDF (.pdf)',
                                'webp' => 'WebP (.webp)',
                            ])
                            ->default(['jpg', 'jpeg', 'png', 'pdf'])
                            ->helperText('File types users can upload'),
                        TextInput::make('max_file_size')
                            ->label('Max File Size (KB)')
                            ->numeric()
                            ->default(5120)
                            ->suffix('KB')
                            ->helperText('Maximum file size in kilobytes (5120 KB = 5 MB)'),
                    ]),
                ]),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Only active templates are shown to users')
                        ->default(true),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(KycDocumentTemplate::query()->withCount('kycVerifications'))
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('name')
                    ->label('Document Type')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => Str::limit($record->description, 50)),

                TextColumn::make('requirements_summary')
                    ->label('Requirements')
                    ->state(function ($record) {
                        $requirements = [];
                        if ($record->requires_front_image) {
                            $requirements[] = 'Front';
                        }
                        if ($record->requires_back_image) {
                            $requirements[] = 'Back';
                        }
                        if ($record->requires_selfie) {
                            $requirements[] = 'Selfie';
                        }
                        if ($record->requires_document_number) {
                            $requirements[] = 'Doc #';
                        }

                        return implode(', ', $requirements) ?: 'None';
                    })
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('kyc_verifications_count')
                    ->label('Submissions')
                    ->counts('kycVerifications')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('max_file_size_for_humans')
                    ->label('Max Size')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    TableAction::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn ($record) => $record->name)
                        ->modalWidth(Width::Large)
                        ->modalContent(fn ($record) => view('filament.pages.kyc-template-details', ['template' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),

                    TableAction::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->modalHeading(fn ($record) => "Edit: {$record->name}")
                        ->modalWidth(Width::ExtraLarge)
                        ->fillForm(fn ($record) => [
                            'name' => $record->name,
                            'description' => $record->description,
                            'instructions' => $record->instructions,
                            'is_required' => $record->is_required,
                            'requires_front_image' => $record->requires_front_image,
                            'requires_back_image' => $record->requires_back_image,
                            'requires_selfie' => $record->requires_selfie,
                            'requires_document_number' => $record->requires_document_number,
                            'accepted_formats' => $record->accepted_formats,
                            'max_file_size' => $record->max_file_size,
                            'sort_order' => $record->sort_order,
                            'is_active' => $record->is_active,
                        ])
                        ->form($this->getTemplateForm())
                        ->action(function ($record, array $data) {
                            $record->update([
                                'name' => $data['name'],
                                'slug' => Str::slug($data['name']),
                                'description' => $data['description'],
                                'instructions' => $data['instructions'],
                                'is_required' => $data['is_required'],
                                'requires_front_image' => $data['requires_front_image'],
                                'requires_back_image' => $data['requires_back_image'],
                                'requires_selfie' => $data['requires_selfie'],
                                'requires_document_number' => $data['requires_document_number'],
                                'accepted_formats' => $data['accepted_formats'],
                                'max_file_size' => $data['max_file_size'],
                                'sort_order' => $data['sort_order'],
                                'is_active' => $data['is_active'],
                            ]);

                            Notification::make()
                                ->title('Template Updated')
                                ->success()
                                ->send();
                        }),

                    TableAction::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['is_active' => ! $record->is_active]);

                            Notification::make()
                                ->title($record->is_active ? 'Template Activated' : 'Template Deactivated')
                                ->success()
                                ->send();
                        }),

                    TableAction::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function ($record) {
                            $newTemplate = $record->replicate();
                            $newTemplate->name = $record->name.' (Copy)';
                            $newTemplate->slug = Str::slug($newTemplate->name);
                            $newTemplate->save();

                            Notification::make()
                                ->title('Template Duplicated')
                                ->body("A copy of '{$record->name}' has been created.")
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Template')
                        ->modalDescription(fn ($record) => "Are you sure you want to delete '{$record->name}'? This will not affect existing KYC submissions."),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No document templates')
            ->emptyStateDescription('Create document templates to define what users need to submit for KYC verification.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                TableAction::make('create_first')
                    ->label('Create First Template')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create Document Template')
                    ->modalWidth(Width::ExtraLarge)
                    ->form($this->getTemplateForm())
                    ->action(function (array $data) {
                        KycDocumentTemplate::create([
                            'name' => $data['name'],
                            'slug' => Str::slug($data['name']),
                            'description' => $data['description'],
                            'instructions' => $data['instructions'],
                            'is_required' => $data['is_required'],
                            'requires_front_image' => $data['requires_front_image'],
                            'requires_back_image' => $data['requires_back_image'],
                            'requires_selfie' => $data['requires_selfie'],
                            'requires_document_number' => $data['requires_document_number'],
                            'accepted_formats' => $data['accepted_formats'],
                            'max_file_size' => $data['max_file_size'],
                            'sort_order' => $data['sort_order'],
                            'is_active' => $data['is_active'],
                        ]);

                        Notification::make()
                            ->title('Template Created')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}

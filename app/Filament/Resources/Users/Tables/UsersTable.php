<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Mail\AdminNotificationMail;
use App\Mail\NewsletterMail;
use App\Mail\PushNotificationMail;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'User')
                    ->color(fn ($state) => $state?->color() ?? 'gray')
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kyc_level')
                    ->label('KYC Level')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(UserRole::options())
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
                    EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->modalHeading('Edit user')
                        ->modalWidth('2xl')
                        ->model(User::class)
                        ->schema(fn (Schema $schema) => UserForm::configure($schema->columns(1))),
                    Action::make('email')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Send Email')
                        ->modalDescription('Send an email to this user')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('subject')
                                ->label('Subject')
                                ->required(),
                            \Filament\Forms\Components\MarkdownEditor::make('message')
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
                        ])
                        ->action(function (User $record, array $data): void {
                            // Send email notification
                            Mail::to($record->email)->send(
                                new AdminNotificationMail(
                                    user: $record,
                                    subject: $data['subject'],
                                    message: $data['message'],
                                )
                            );

                            // Store database notification for the user
                            $record->notify(
                                GeneralNotification::adminMessage(
                                    $data['subject'],
                                    strip_tags($data['message'])
                                )
                            );

                            // Log the activity
                            ActivityLogger::logAdmin(
                                'user_email_sent',
                                $record,
                                auth()->user(),
                                [
                                    'subject' => $data['subject'],
                                    'target_user_email' => $record->email,
                                ]
                            );

                            Notification::make()
                                ->title('Email Sent')
                                ->body("Email sent successfully to {$record->email}")
                                ->success()
                                ->send();
                        }),
                    Action::make('push')
                        ->icon('heroicon-o-bell')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Send Push Notification')
                        ->modalDescription('Send a push notification to this user')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('title')
                                ->label('Title')
                                ->required(),
                            \Filament\Forms\Components\MarkdownEditor::make('message')
                                ->label('Message')
                                ->required()
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
                                ]),
                        ])
                        ->action(function (User $record, array $data): void {
                            // Send push notification via email (can be extended to actual push service)
                            Mail::to($record->email)->send(
                                new PushNotificationMail(
                                    user: $record,
                                    title: $data['title'],
                                    message: $data['message'],
                                )
                            );

                            // Store database notification for the user
                            $record->notify(
                                GeneralNotification::adminMessage(
                                    $data['title'],
                                    strip_tags($data['message'])
                                )
                            );

                            // Log the activity
                            ActivityLogger::logAdmin(
                                'push_notification_sent',
                                $record,
                                auth()->user(),
                                [
                                    'title' => $data['title'],
                                    'target_user_email' => $record->email,
                                ]
                            );

                            Notification::make()
                                ->title('Push Notification Sent')
                                ->body("Notification sent successfully to {$record->email}")
                                ->success()
                                ->send();
                        }),
                    Action::make('newsletter')
                        ->icon('heroicon-o-newspaper')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Send Newsletter')
                        ->modalDescription('Send newsletter to this user')
                        ->form([
                            \Filament\Forms\Components\Select::make('template')
                                ->label('Newsletter Template')
                                ->options([
                                    'weekly' => 'Weekly Newsletter',
                                    'monthly' => 'Monthly Newsletter',
                                    'promo' => 'Promotional Newsletter',
                                ])
                                ->required(),
                            \Filament\Forms\Components\MarkdownEditor::make('custom_message')
                                ->label('Custom Message (Optional)')
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'link',
                                    'bulletList',
                                    'orderedList',
                                ])
                                ->disableToolbarButtons([
                                    'attachFiles',
                                    'codeBlock',
                                    'heading',
                                ]),
                        ])
                        ->action(function (User $record, array $data): void {
                            // Send newsletter
                            Mail::to($record->email)->send(
                                new NewsletterMail(
                                    user: $record,
                                    template: $data['template'],
                                    customMessage: $data['custom_message'] ?? null,
                                )
                            );

                            // Log the activity
                            ActivityLogger::logAdmin(
                                'newsletter_sent',
                                $record,
                                auth()->user(),
                                [
                                    'template' => $data['template'],
                                    'target_user_email' => $record->email,
                                ]
                            );

                            Notification::make()
                                ->title('Newsletter Sent')
                                ->body("Newsletter sent successfully to {$record->email}")
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

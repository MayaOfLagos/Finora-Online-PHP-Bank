<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Ticket number copied')
                    ->weight('semibold'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->subject),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (TicketPriority $state): string => match($state->value) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TicketStatus $state): string => match($state->value) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('assignedAgent.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalWidth('4xl')
                        ->modalHeading(fn ($record) => 'Ticket #' . $record->ticket_number)
                        ->modalDescription(fn ($record) => $record->subject),
                    
                    Action::make('reply')
                        ->label('Reply')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->modalWidth('2xl')
                        ->modalHeading(fn ($record) => 'Reply to Ticket #' . $record->ticket_number)
                        ->form([
                            Textarea::make('message')
                                ->label('Your Reply')
                                ->required()
                                ->rows(5)
                                ->placeholder('Type your response here...')
                                ->helperText('This message will be sent to the user'),
                        ])
                        ->action(function ($record, array $data) {
                            // Create a ticket message
                            $message = \App\Models\TicketMessage::create([
                                'support_ticket_id' => $record->id,
                                'user_id' => auth()->id(),
                                'message' => $data['message'],
                                'type' => 'agent',
                            ]);

                            // Update ticket status if it's resolved or closed
                            if (in_array($record->status->value, ['resolved', 'closed'])) {
                                $record->update(['status' => 'in_progress']);
                            }

                            // Send email notification to user
                            \Illuminate\Support\Facades\Mail::to($record->user->email)
                                ->send(new \App\Mail\TicketRepliedMail($record, $message));

                            // Send push notification
                            \Illuminate\Support\Facades\Mail::to($record->user->email)
                                ->send(new \App\Mail\PushNotificationMail(
                                    $record->user,
                                    'New Reply on Ticket',
                                    "A support agent has replied to your ticket #{$record->ticket_number}"
                                ));

                            // Create activity log
                            \App\Services\ActivityLogger::logSupport(
                                'ticket_replied',
                                $record,
                                $record->user,
                                ['message' => $data['message']]
                            );

                            Notification::make()
                                ->title('Reply Sent')
                                ->body('Your reply has been added to the ticket.')
                                ->success()
                                ->send();
                        }),

                    Action::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->modalWidth('md')
                        ->modalHeading('Change Ticket Status')
                        ->form([
                            Select::make('status')
                                ->label('New Status')
                                ->options(TicketStatus::class)
                                ->required()
                                ->default(fn ($record) => $record->status->value),
                            Textarea::make('note')
                                ->label('Note (Optional)')
                                ->rows(3)
                                ->placeholder('Add a note about this status change...'),
                        ])
                        ->action(function ($record, array $data) {
                            // Store old values BEFORE update
                            $oldStatus = $record->status->label();
                            $oldStatusValue = $record->status->value;
                            
                            // Get new status label
                            $newStatus = $data['status']->label();
                            
                            $updates = ['status' => $data['status']];
                            
                            if ($data['status']->value === 'resolved' && !$record->resolved_at) {
                                $updates['resolved_at'] = now();
                            }
                            
                            if ($data['status']->value === 'closed' && !$record->closed_at) {
                                $updates['closed_at'] = now();
                            }

                            $record->update($updates);

                            // Add a message if note provided
                            if (!empty($data['note'])) {
                                \App\Models\TicketMessage::create([
                                    'support_ticket_id' => $record->id,
                                    'user_id' => auth()->id(),
                                    'message' => $data['note'],
                                    'type' => 'agent',
                                ]);
                            }

                            // Send email notification to user
                            \Illuminate\Support\Facades\Mail::to($record->user->email)
                                ->send(new \App\Mail\TicketStatusChangedMail($record, $oldStatus, $newStatus));

                            // Send push notification
                            \Illuminate\Support\Facades\Mail::to($record->user->email)
                                ->send(new \App\Mail\PushNotificationMail(
                                    $record->user,
                                    'Ticket Status Updated',
                                    "Your ticket #{$record->ticket_number} status changed from {$oldStatus} to {$newStatus}"
                                ));

                            // Create activity log
                            \App\Services\ActivityLogger::logSupport(
                                'ticket_status_changed',
                                $record,
                                $record->user,
                                [
                                    'old_status' => $oldStatusValue,
                                    'new_status' => $data['status'],
                                    'note' => $data['note'] ?? null
                                ]
                            );

                            Notification::make()
                                ->title('Status Updated')
                                ->body("Status changed from {$oldStatus} to {$newStatus}")
                                ->success()
                                ->send();
                        }),

                    Action::make('assign')
                        ->label('Assign To')
                        ->icon('heroicon-o-user-plus')
                        ->color('primary')
                        ->modalWidth('md')
                        ->modalHeading('Assign Ticket')
                        ->form([
                            Select::make('assigned_to')
                                ->label('Assign To')
                                ->relationship('assignedAgent', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->email . ')')
                                ->searchable(['first_name', 'last_name', 'email'])
                                ->required()
                                ->preload()
                                ->default(fn ($record) => $record->assigned_to),
                        ])
                        ->action(function ($record, array $data) {
                            // Get old assignee ID from database attribute (not relationship)
                            $oldAssigneeId = $record->getOriginal('assigned_to');
                            $oldAssignee = $oldAssigneeId 
                                ? \App\Models\User::find($oldAssigneeId)->name 
                                : 'Unassigned';
                            
                            // Get new assignee
                            $newAssignee = \App\Models\User::find($data['assigned_to'])->name;
                            
                            $record->update(['assigned_to' => $data['assigned_to']]);

                            Notification::make()
                                ->title('Ticket Assigned')
                                ->body("Ticket assigned from {$oldAssignee} to {$newAssignee}")
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Ticket')
                        ->modalDescription('Are you sure you want to delete this ticket? This action cannot be undone.')
                        ->successNotificationTitle('Ticket Deleted'),
                ])
                    ->button()
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

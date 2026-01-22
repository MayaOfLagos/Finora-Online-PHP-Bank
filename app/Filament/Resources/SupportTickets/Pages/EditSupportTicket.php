<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\Schemas\SupportTicketForm;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Mail\PushNotificationMail;
use App\Mail\TicketRepliedMail;
use App\Models\TicketMessage;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Actions as FormActions;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    public $new_message = '';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Chat Messages')
                    ->description('Conversation history between support and user')
                    ->schema([
                        ViewField::make('messages')
                            ->label('')
                            ->view('filament.resources.support-tickets.chat-messages'),

                        MarkdownEditor::make('new_message')
                            ->label('Reply to Ticket')
                            ->placeholder('Type your response here...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                            ])
                            ->minHeight(150)
                            ->maxHeight(300)
                            ->live()
                            ->dehydrated(false)
                            ->hintAction(
                                FormAction::make('send_message')
                                    ->label('Send')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->color('primary')
                                    ->action(function () {
                                        $this->sendMessage();
                                    })
                            ),
                    ])
                    ->collapsible()
                    ->id('chat-section'),

                ...SupportTicketForm::configure($schema)->getComponents(),
            ]);
    }

    public function sendMessage(): void
    {
        $messageContent = $this->data['new_message'] ?? '';

        if (empty($messageContent)) {
            Notification::make()
                ->title('Message Required')
                ->body('Please type a message before sending.')
                ->warning()
                ->send();
            return;
        }

        // Create the message
        $message = TicketMessage::create([
            'support_ticket_id' => $this->record->id,
            'user_id' => auth()->id(),
            'message' => $messageContent,
            'type' => 'agent',
        ]);

        // Update ticket status to in_progress if it's open
        if ($this->record->status->value === 'open') {
            $this->record->update(['status' => 'in_progress']);
        }

        // Send email notification
        try {
            Mail::to($this->record->user->email)
                ->send(new TicketRepliedMail($this->record, $message));
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket reply email: ' . $e->getMessage());
        }

        // Send push notification
        try {
            Mail::to($this->record->user->email)
                ->send(new PushNotificationMail(
                    $this->record->user,
                    'New Reply on Ticket',
                    "A support agent has replied to your ticket #{$this->record->ticket_number}"
                ));
        } catch (\Exception $e) {
            \Log::error('Failed to send ticket reply push notification: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogger::logSupport(
            'ticket_replied',
            $this->record,
            $this->record->user,
            ['message' => $messageContent]
        );

        // Clear the message field
        $this->data['new_message'] = '';

        Notification::make()
            ->title('Message Sent')
            ->body('Your reply has been sent to the user.')
            ->success()
            ->send();

        // Refresh the page to show the new message
        $this->redirect(request()->header('Referer'));
    }
}

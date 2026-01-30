<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket Information')
                    ->description('Basic ticket details')
                    ->schema([
                        TextInput::make('ticket_number')
                            ->label('Ticket Number')
                            ->default(fn (): string => 'TKT-'.strtoupper(substr(uniqid(), -8)))
                            ->required()
                            ->maxLength(255)
                            ->suffixIcon('heroicon-o-ticket')
                            ->helperText('Auto-generated ticket number')
                            ->columnSpanFull(),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextEntry::make('initial_message')
                            ->label('Initial Message')
                            ->state(fn ($record) => $record?->messages()->where('type', 'customer')->oldest()->first()?->message ?? 'No message')
                            ->columnSpanFull()
                            ->prose()
                            ->markdown(),
                    ])
                    ->columns(2),

                Section::make('Priority & Status')
                    ->schema([
                        Select::make('priority')
                            ->label('Priority')
                            ->options(TicketPriority::class)
                            ->default('medium')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(TicketStatus::class)
                            ->default('open')
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Assign To')
                            ->relationship('assignedAgent', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->email.')')
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->placeholder('Not assigned')
                            ->preload()
                            ->helperText('Assign to an admin user'),
                    ])
                    ->columns(3),

                Section::make('Resolution')
                    ->schema([
                        DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                        DateTimePicker::make('closed_at')
                            ->label('Closed At')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('uuid')
                            ->label('UUID')
                            ->default(fn (): string => (string) Str::uuid())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}

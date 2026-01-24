<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use App\Models\Card;
use App\Models\CardType;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Filament\Tables;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => $this->activeTab === 'all'),

            Actions\Action::make('create_card_type')
                ->label('Create Card Type')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->visible(fn () => $this->activeTab === 'card-types')
                ->modalWidth(Width::TwoExtraLarge)
                ->form([
                    Section::make('Card Type Information')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('e.g., Gold Card, Platinum Card'),

                                    TextInput::make('code')
                                        ->required()
                                        ->maxLength(50)
                                        ->unique(CardType::class, 'code')
                                        ->placeholder('e.g., GOLD, PLATINUM')
                                        ->helperText('Unique identifier code'),

                                    TextInput::make('default_limit')
                                        ->label('Default Limit ($)')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->helperText('Default spending/credit limit'),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    Toggle::make('is_virtual')
                                        ->label('Virtual Card')
                                        ->default(false)
                                        ->inline(false),

                                    Toggle::make('is_credit')
                                        ->label('Credit Card')
                                        ->default(false)
                                        ->inline(false)
                                        ->helperText('Otherwise debit card'),

                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true)
                                        ->inline(false),
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    // Convert limit to cents
                    $data['default_limit'] = (int) ($data['default_limit'] * 100);

                    CardType::create($data);

                    Notification::make()
                        ->title('Card Type Created')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Cards')
                ->badge(fn () => Card::count()),

            'card-types' => Tab::make('Card Types')
                ->badge(fn () => CardType::count()),
        ];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($this->activeTab === 'card-types') {
            return CardType::query()->withCount('cards');
        }

        return parent::getTableQuery();
    }

    public function table(Tables\Table $table): Tables\Table
    {
        if ($this->activeTab === 'card-types') {
            return $table
                ->columns([
                    Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('code')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->color('info'),

                    Tables\Columns\TextColumn::make('default_limit')
                        ->label('Default Limit')
                        ->formatStateUsing(fn ($state) => '$'.number_format($state / 100, 2))
                        ->sortable(),

                    Tables\Columns\IconColumn::make('is_virtual')
                        ->label('Virtual')
                        ->boolean(),

                    Tables\Columns\IconColumn::make('is_credit')
                        ->label('Credit')
                        ->boolean(),

                    Tables\Columns\IconColumn::make('is_active')
                        ->label('Active')
                        ->boolean(),

                    Tables\Columns\TextColumn::make('cards_count')
                        ->label('Cards Issued')
                        ->badge()
                        ->color('success'),

                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->filters([])
                ->recordActions(\App\Filament\Resources\Cards\Tables\CardTypesTable::getActions())
                ->defaultSort('created_at', 'desc');
        }

        return parent::table($table);
    }
}

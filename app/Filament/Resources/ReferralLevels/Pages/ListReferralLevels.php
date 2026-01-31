<?php

namespace App\Filament\Resources\ReferralLevels\Pages;

use App\Filament\Resources\ReferralLevels\ReferralLevelResource;
use App\Filament\Resources\ReferralLevels\Schemas\ReferralLevelForm;
use App\Models\ReferralLevel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ListReferralLevels extends ListRecords
{
    protected static string $resource = ReferralLevelResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Referral Levels';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $count = ReferralLevel::active()->count();

        return "{$count} active level".($count !== 1 ? 's' : '').' configured';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quick_setup')
                ->label('Quick Setup')
                ->icon(Heroicon::OutlinedBolt)
                ->color('warning')
                ->modalHeading('Quick Level Setup')
                ->modalDescription('Automatically generate referral levels with sensible defaults. This will NOT delete existing levels.')
                ->modalWidth(Width::Medium)
                ->form([
                    Section::make()
                        ->schema([
                            Select::make('levels_count')
                                ->label('Number of Levels to Generate')
                                ->options([
                                    3 => '3 Levels (Starter, Bronze, Silver)',
                                    5 => '5 Levels (Starter to Platinum)',
                                    7 => '7 Levels (Starter to Elite)',
                                    10 => '10 Levels (Full Tier System)',
                                ])
                                ->default(5)
                                ->required()
                                ->helperText('Select how many levels you want to create'),
                        ]),
                ])
                ->action(function (array $data) {
                    $count = (int) $data['levels_count'];

                    ReferralLevel::generateDefaultLevels($count);

                    Notification::make()
                        ->title('Levels Generated!')
                        ->body("{$count} referral levels have been created with default values.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => ReferralLevel::count() === 0),

            Action::make('reset_levels')
                ->label('Reset All Levels')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset All Referral Levels')
                ->modalDescription('This will DELETE all existing levels and regenerate them with default values. This action cannot be undone.')
                ->modalWidth(Width::Medium)
                ->form([
                    Section::make()
                        ->schema([
                            Select::make('levels_count')
                                ->label('Number of Levels to Generate')
                                ->options([
                                    3 => '3 Levels',
                                    5 => '5 Levels',
                                    7 => '7 Levels',
                                    10 => '10 Levels',
                                ])
                                ->default(5)
                                ->required(),
                        ]),
                ])
                ->action(function (array $data) {
                    $count = (int) $data['levels_count'];

                    // Delete all existing levels
                    ReferralLevel::truncate();

                    // Generate new levels
                    ReferralLevel::generateDefaultLevels($count);

                    Notification::make()
                        ->title('Levels Reset!')
                        ->body("All levels have been reset. {$count} new levels created.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => ReferralLevel::count() > 0),

            CreateAction::make()
                ->label('Create Level')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->modalWidth(Width::TwoExtraLarge)
                ->form(fn () => ReferralLevelForm::configure(new \Filament\Schemas\Schema(app()))->getComponents()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ReferralLevels\Widgets\ReferralLevelStatsOverview::class,
        ];
    }
}

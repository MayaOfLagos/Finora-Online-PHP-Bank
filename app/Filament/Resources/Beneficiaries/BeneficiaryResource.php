<?php

namespace App\Filament\Resources\Beneficiaries;

use App\Filament\Resources\Beneficiaries\Pages\ListBeneficiaries;
use App\Filament\Resources\Beneficiaries\Schemas\BeneficiaryForm;
use App\Filament\Resources\Beneficiaries\Tables\BeneficiariesTable;
use App\Models\Beneficiary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BeneficiaryResource extends Resource
{
    protected static ?string $model = Beneficiary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Beneficiaries';

    protected static ?string $pluralModelLabel = 'Beneficiaries';

    protected static ?string $modelLabel = 'Beneficiary';

    public static function form(Schema $schema): Schema
    {
        return BeneficiaryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeneficiaries::route('/'),
        ];
    }
}

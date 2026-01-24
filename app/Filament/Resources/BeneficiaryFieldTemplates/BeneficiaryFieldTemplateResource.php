<?php

namespace App\Filament\Resources\BeneficiaryFieldTemplates;

use App\Filament\Resources\BeneficiaryFieldTemplates\Pages\CreateBeneficiaryFieldTemplate;
use App\Filament\Resources\BeneficiaryFieldTemplates\Pages\EditBeneficiaryFieldTemplate;
use App\Filament\Resources\BeneficiaryFieldTemplates\Pages\ListBeneficiaryFieldTemplates;
use App\Filament\Resources\BeneficiaryFieldTemplates\Schemas\BeneficiaryFieldTemplateForm;
use App\Filament\Resources\BeneficiaryFieldTemplates\Tables\BeneficiaryFieldTemplatesTable;
use App\Models\BeneficiaryFieldTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BeneficiaryFieldTemplateResource extends Resource
{
    protected static ?string $model = BeneficiaryFieldTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Beneficiary Fields';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return BeneficiaryFieldTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiaryFieldTemplatesTable::configure($table);
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
            'index' => ListBeneficiaryFieldTemplates::route('/'),
            'create' => CreateBeneficiaryFieldTemplate::route('/create'),
            'edit' => EditBeneficiaryFieldTemplate::route('/{record}/edit'),
        ];
    }
}

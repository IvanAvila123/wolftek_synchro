<?php

namespace App\Filament\Resources\Adjustments;

use App\Filament\Resources\Adjustments\Pages\CreateAdjustment;
use App\Filament\Resources\Adjustments\Pages\EditAdjustment;
use App\Filament\Resources\Adjustments\Pages\ListAdjustments;
use App\Filament\Resources\Adjustments\Pages\ViewAdjustment;
use App\Filament\Resources\Adjustments\Schemas\AdjustmentForm;
use App\Filament\Resources\Adjustments\Schemas\AdjustmentInfolist;
use App\Filament\Resources\Adjustments\Tables\AdjustmentsTable;
use App\Models\Adjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdjustmentResource extends Resource
{
    protected static ?string $model = Adjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Control de Mermas y Devoluciones';
    

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdjustmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdjustmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdjustmentsTable::configure($table);
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
            'index' => ListAdjustments::route('/'),
            'view' => ViewAdjustment::route('/{record}'),
        ];
    }
}

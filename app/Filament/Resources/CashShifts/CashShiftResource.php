<?php

namespace App\Filament\Resources\CashShifts;

use App\Filament\Resources\CashShifts\Pages\CreateCashShift;
use App\Filament\Resources\CashShifts\Pages\EditCashShift;
use App\Filament\Resources\CashShifts\Pages\ListCashShifts;
use App\Filament\Resources\CashShifts\Schemas\CashShiftForm;
use App\Filament\Resources\CashShifts\Tables\CashShiftsTable;
use App\Models\CashShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashShiftResource extends Resource
{
    protected static ?string $model = CashShift::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $navigationLabel = 'Control de Cajas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Turno de Caja';

    protected static ?string $pluralModelLabel = 'Turnos de Caja';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventas';

    public static function form(Schema $schema): Schema
    {
        return CashShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashShiftsTable::configure($table);
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
            'index' => ListCashShifts::route('/'),
            'create' => CreateCashShift::route('/create'),
        ];
    }
}

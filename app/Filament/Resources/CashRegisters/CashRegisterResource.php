<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Resources\CashRegisters\Pages\CreateCashRegister;
use App\Filament\Resources\CashRegisters\Pages\EditCashRegister;
use App\Filament\Resources\CashRegisters\Pages\ListCashRegisters;
use App\Filament\Resources\CashRegisters\Pages\ViewCashRegister;
use App\Filament\Resources\CashRegisters\Schemas\CashRegisterForm;
use App\Filament\Resources\CashRegisters\Schemas\CashRegisterInfolist;
use App\Filament\Resources\CashRegisters\Tables\CashRegistersTable;
use App\Models\CashRegister;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    // Cambiamos el ícono por una computadora
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Cajas Registradoras';
    protected static ?string $modelLabel = 'Caja Registradora';
    protected static ?string $pluralModelLabel = 'Cajas Registradoras';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventas';

    public static function form(Schema $schema): Schema
    {
        return CashRegisterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CashRegisterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashRegistersTable::configure($table);
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
            'index' => ListCashRegisters::route('/'),
            'create' => CreateCashRegister::route('/create'),
            'view' => ViewCashRegister::route('/{record}'),
            'edit' => EditCashRegister::route('/{record}/edit'),
        ];
    }
}
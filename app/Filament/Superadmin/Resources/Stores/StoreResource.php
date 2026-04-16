<?php

namespace App\Filament\Superadmin\Resources\Stores;

use App\Filament\Superadmin\Resources\Stores\Pages\CreateStore;
use App\Filament\Superadmin\Resources\Stores\Pages\EditStore;
use App\Filament\Superadmin\Resources\Stores\Pages\ListStores;
use App\Filament\Superadmin\Resources\Stores\Pages\ViewStore;
use App\Filament\Superadmin\Resources\Stores\Schemas\StoreForm;
use App\Filament\Superadmin\Resources\Stores\Schemas\StoreInfolist;
use App\Filament\Superadmin\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $modelLabel = 'Tienda';

    protected static ?string $pluralModelLabel = 'Tiendas';

    protected static string|\UnitEnum|null $navigationGroup = 'SaaS';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StoreForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StoreInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoresTable::configure($table);
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
            'index' => ListStores::route('/'),
            'create' => CreateStore::route('/create'),
            'view' => ViewStore::route('/{record}'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }
}

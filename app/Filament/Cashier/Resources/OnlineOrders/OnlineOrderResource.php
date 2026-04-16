<?php

namespace App\Filament\Cashier\Resources\OnlineOrders;

use App\Filament\Cashier\Resources\OnlineOrders\Pages\CreateOnlineOrder;
use App\Filament\Cashier\Resources\OnlineOrders\Pages\EditOnlineOrder;
use App\Filament\Cashier\Resources\OnlineOrders\Pages\ListOnlineOrders;
use App\Filament\Cashier\Resources\OnlineOrders\Pages\ViewOnlineOrder;
use App\Filament\Cashier\Resources\OnlineOrders\Schemas\OnlineOrderForm;
use App\Filament\Cashier\Resources\OnlineOrders\Schemas\OnlineOrderInfolist;
use App\Filament\Cashier\Resources\OnlineOrders\Tables\OnlineOrdersTable;
use App\Models\OnlineOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OnlineOrderResource extends Resource
{
    protected static ?string $model = OnlineOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'Pedidos en Línea';

    protected static ?string $modelLabel = 'Pedido en Línea';

    protected static ?string $pluralModelLabel = 'Pedidos en Línea';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OnlineOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OnlineOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnlineOrdersTable::configure($table);
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
            'index' => ListOnlineOrders::route('/'),
            'view' => ViewOnlineOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('store_id', filament()->getTenant()?->id)
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }
}

<?php

namespace App\Filament\Resources\OnlineOrders;

use App\Filament\Resources\OnlineOrders\Pages\ListOnlineOrders;
use App\Filament\Resources\OnlineOrders\Pages\ViewOnlineOrder;
use App\Models\OnlineOrder;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Cashier\Resources\OnlineOrders\Schemas\OnlineOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->sortable()
                    ->searchable()
                    ->prefix('#00'),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('customer_phone')
                    ->label('Teléfono'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'ready'     => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'   => 'Pendiente',
                        'ready'     => 'Listo',
                        'completed' => 'Entregado',
                        'cancelled' => 'Cancelado',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending'   => 'Pendientes',
                        'ready'     => 'Listos',
                        'completed' => 'Completados',
                        'cancelled' => 'Cancelados',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnlineOrders::route('/'),
            'view'  => ViewOnlineOrder::route('/{record}'),
        ];
    }
}

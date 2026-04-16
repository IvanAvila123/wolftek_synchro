<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Factura y Datos')
                ->description('informacion de la venta')
                ->columns(2)
                ->schema([
                    TextEntry::make('store.name')
                    ->label('Tienda'),
                TextEntry::make('supplier.company')
                    ->label('Proveedor'),
                TextEntry::make('total')
                    ->label('Total')
                    ->money('MXN'),
                TextEntry::make('estatus')
                    ->label('Estatus')
                    ->color(fn(string $state): string => match ($state) {
                        'pagada' => 'success',
                        'pendiente' => 'warning',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->badge(),
                TextEntry::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('Usuario')
                    ->placeholder('-'),
                ])
            ]);
    }
}

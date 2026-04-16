<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->icon('heroicon-o-cube')
                    ->iconColor('primary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('category.name')
                            ->label('Categoría')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('barcode')
                            ->label('Código de barras')
                            ->placeholder('Sin código'),

                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ]),

                Section::make('Precios y Unidad')
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('price_buy')
                            ->label('Precio de compra')
                            ->money('MXN')
                            ->placeholder('No registrado'),

                        TextEntry::make('price_sell')
                            ->label('Precio de venta')
                            ->money('MXN'),

                        TextEntry::make('unidad')
                            ->label('Unidad')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pieza' => 'Pieza',
                                'kg' => 'Kilogramo',
                                'litro' => 'Litro',
                                'gramo' => 'Gramo',
                                default => $state,
                            })
                            ->badge(),

                        IconEntry::make('has_scale')
                            ->label('Venta por peso')
                            ->boolean(),
                    ]),

                Section::make('Inventario')
                    ->icon('heroicon-o-archive-box')
                    ->iconColor('warning')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('stock')
                            ->label('Stock actual')
                            ->color(fn ($record): string =>
                                $record->stock <= $record->stock_min ? 'danger' : 'success'
                            )
                            ->weight('bold')
                            ->icon(fn ($record): ?string =>
                                $record->stock <= $record->stock_min ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'
                            ),

                        TextEntry::make('stock_min')
                            ->label('Stock mínimo'),

                        IconEntry::make('is_active')
                            ->label('Activo')
                            ->boolean(),
                    ]),
            ]);
    }
}
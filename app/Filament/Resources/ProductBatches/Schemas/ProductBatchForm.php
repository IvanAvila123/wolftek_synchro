<?php

namespace App\Filament\Resources\ProductBatches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registro de Lote')
                    ->description('Ingresa cuántas piezas entraron y cuándo caducan.')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Cantidad de piezas en este lote')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        DatePicker::make('expiry_date')
                            ->label('Fecha de Caducidad')
                            ->required()
                            ->minDate(now())
                            ->native(false)
                            ->displayFormat('d / M / Y'),
                    ])
            ]);
    }
}

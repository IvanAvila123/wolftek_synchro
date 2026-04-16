<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Marcelorodrigo\FilamentBarcodeScannerField\Forms\Components\BarcodeInput;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Utilities\Set;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del producto')
                    ->description('Datos generales del producto')
                    ->icon('heroicon-o-cube')
                    ->iconColor('primary')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del producto')
                            ->placeholder('Ej: Coca-Cola 600ml')
                            ->maxLength(255)
                            ->autofocus()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nombre de categoría')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->required(),

                        TextInput::make('plu')
                            ->label('PLU de báscula')
                            ->helperText('Número programado en tu báscula de precio (ej. 1, 2, 3...)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99999)
                            ->nullable()
                            ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get) => (bool) $get('has_scale'))
                            ->placeholder('Ej: 1'),

                        BarcodeInput::make('barcode')
                            ->label('Código de barras')
                            ->placeholder('Escanea (pistola/cámara) o escribe el código')
                            ->icon('heroicon-o-qr-code')
                            ->extraInputAttributes([
                                'pattern' => '[0-9]*', 
                                'x-on:keydown.enter.prevent' => '$el.blur()',
                            ])
                            ->regex('/^[0-9]+$/')
                            ->validationMessages([
                                'regex' => 'El código de barras solo debe contener números.',
                            ])
                            ->unique(ignoreRecord: true)
                            // 👇 NUEVO: Lo cambiamos a hintAction para que no pelee con el plugin
                            ->hintAction(
                                Action::make('generar')
                                    ->label('Generar aleatorio') // Ahora tendrá texto
                                    ->icon('heroicon-m-sparkles') // Un iconito de magia
                                    ->action(function (Set $set){
                                        $codigoInterno = rand(100000, 999999) . rand(100000, 999999);
                                        $set('barcode', $codigoInterno);
                                    })
                            )
                            ->nullable(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Descripción opcional del producto')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Precios y Unidad')
                    ->description('Configuración de precios y tipo de venta')
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price_buy')
                            ->label('Precio de compra')
                            ->placeholder('0.00')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->nullable(),

                        TextInput::make('price_sell')
                            ->label('Precio de venta')
                            ->placeholder('0.00')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->required(),

                        Select::make('unidad')
                            ->label('Unidad de medida')
                            ->options([
                                'pieza' => 'Pieza',
                                'kg' => 'Kilogramo',
                                'litro' => 'Litro',
                                'gramo' => 'Gramo',
                            ])
                            ->default('pieza')
                            ->required(),

                        Toggle::make('has_scale')
                            ->label('Se vende por peso')
                            ->helperText('Activa si el producto se pesa en báscula')
                            ->live()
                            ->default(false),
                    ]),

                Section::make('Inventario')
                    ->description('Control de existencias')
                    ->icon('heroicon-o-archive-box')
                    ->iconColor('warning')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stock')
                            ->label('Stock actual')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        TextInput::make('stock_min')
                            ->label('Stock mínimo')
                            ->helperText('Se te alertará cuando el stock baje de este número')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Producto activo')
                            ->helperText('Desactívalo para ocultarlo sin eliminarlo')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

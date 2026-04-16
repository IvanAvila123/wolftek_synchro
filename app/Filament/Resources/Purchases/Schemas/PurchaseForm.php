<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Factura')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('supplier_id')
                            ->relationship('supplier', 'company') // Muestra "Sabritas" en vez del nombre del repartidor
                            ->label('Proveedor')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('estatus') // Usamos 'estatus' como lo tienes en tu modelo
                            ->label('Estatus de Pago')
                            ->options([
                                'pagada' => 'Pagado al contado',
                                'pendiente' => 'Pendiente (Fiar a crédito)',
                                'cancelada' => 'Orden Cancelada'
                            ])
                            ->default('pagada')
                            ->required(),

                        Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                    ]),

                    Section::make('Mercancía Recibida')
                    ->icon('heroicon-o-archive-box')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship() // Filament sabe conectarlo con la relación items() de tu modelo Purchase
                            ->columns(4)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $producto = Product::find($state);
                                        if ($producto) {
                                            // Auto-llenamos el costo con lo que nos costó la última vez
                                            $set('price_buy', $producto->price_buy);
                                        }
                                    }),

                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $qty = (float) ($get('quantity') ?? 0);
                                        $price_buy = (float) ($get('price_buy') ?? 0);
                                        $set('subtotal', $qty * $price_buy);
                                    }),

                                TextInput::make('price_buy')
                                    ->label('Costo Unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $qty = (float) ($get('quantity') ?? 0);
                                        $price_buy = (float) ($get('price_buy') ?? 0);
                                        $set('subtotal', $qty * $price_buy);
                                    }),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled() // Bloqueado para que el usuario no manipule las matemáticas
                                    ->dehydrated() // Esto fuerza a Filament a guardarlo en la Base de Datos aunque esté deshabilitado
                                    ->default(0),
                            ])
                            ->live() // Avisa al formulario cuando se agregan/quitan filas para recalcular el gran total
                            ->addActionLabel('Añadir otro producto'),
                    ]),

                    Section::make('Resumen')
                    ->schema([
                        // Usamos un Placeholder para mostrar el total en vivo sin complicarnos con el Set
                        Placeholder::make('total_visual')
                            ->label('Total de la Compra')
                            ->content(function (Get $get) {
                                $total = 0;
                                $items = $get('items') ?? [];
                                foreach ($items as $item) {
                                    $total += (float) ($item['subtotal'] ?? 0);
                                }
                                return '$ ' . number_format($total, 2);
                            })
                            ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                    ]),
            ]);
    }
}

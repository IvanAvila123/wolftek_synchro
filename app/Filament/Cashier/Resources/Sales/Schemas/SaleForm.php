<?php

namespace App\Filament\Cashier\Resources\Sales\Schemas;

use App\Models\CashShift;
use App\Models\Customer;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Datos de la venta')
                ->columns(2)
                ->schema([
                    Select::make('cash_shift_id')
                        ->label('Turno de caja')
                        ->options(fn () => CashShift::where('status', 'open')
                            ->whereHas('cashRegister', fn ($q) => $q->where('store_id', Filament::getTenant()->id))
                            ->with('cashRegister')
                            ->get()
                            ->mapWithKeys(fn ($shift) => [$shift->id => "Turno #{$shift->id} — {$shift->cashRegister->name}"])
                        )
                        ->required(),

                    Select::make('customer_id')
                        ->label('Cliente (opcional)')
                        ->options(fn () => Customer::where('store_id', Filament::getTenant()->id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->nullable(),

                    Select::make('payment_method')
                        ->label('Método de pago')
                        ->options([
                            'cash'     => 'Efectivo',
                            'card'     => 'Tarjeta',
                            'transfer' => 'Transferencia',
                            'credit'   => 'Crédito (fiado)',
                        ])
                        ->required(),

                    TextInput::make('discount')
                        ->label('Descuento en venta ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateTotal($set, $get)),
                ]),

            Section::make('Productos')
                ->schema([
                    Repeater::make('items')
                        ->label('')
                        ->relationship()
                        ->schema([

                            // ── Campo de escáner (no se guarda en BD) ──────────────
                            TextInput::make('barcode_scan')
                                ->label('Escanear código de barras')
                                ->placeholder('Apunta la pistola y escanea...')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                    if (blank($state)) return;

                                    $tenantId = Filament::getTenant()->id;

                                    // ── Báscula de precio: EAN-13 que empieza con "2" ──
                                    // Formato Torrey/CAS: 2XXXXXWWWWWC
                                    //   X = PLU (5 dígitos)  W = gramos (5 dígitos)
                                    if (strlen($state) === 13 && str_starts_with($state, '2')) {
                                        $plu         = (int) substr($state, 1, 5);
                                        $weightGrams = (int) substr($state, 6, 5);

                                        $product = Product::where('plu', $plu)
                                            ->where('store_id', $tenantId)
                                            ->first();

                                        if ($product && $weightGrams > 0) {
                                            $qty = round($weightGrams / 1000, 3);
                                            $set('product_id', $product->id);
                                            $set('quantity',   $qty);
                                            $set('price',      $product->price_sell);
                                            $set('discount',   0);
                                            $set('subtotal',   round($qty * $product->price_sell, 2));
                                        }
                                    } else {
                                        // ── Código de barras normal ────────────────────
                                        $product = Product::where('barcode', $state)
                                            ->where('store_id', $tenantId)
                                            ->first();

                                        if ($product) {
                                            $set('product_id', $product->id);
                                            $set('price',      $product->price_sell);
                                            $set('discount',   0);

                                            if (! $product->has_scale) {
                                                $set('quantity', 1);
                                                $set('subtotal', $product->price_sell);
                                            } else {
                                                // Producto por peso sin etiqueta: el cajero teclea el peso
                                                $set('quantity', null);
                                                $set('subtotal', 0);
                                            }
                                        }
                                    }

                                    $set('barcode_scan', null);
                                })
                                ->dehydrated(false)
                                ->extraInputAttributes(['autocomplete' => 'off'])
                                ->columnSpanFull(),

                            // ── Producto ───────────────────────────────────────────
                            Select::make('product_id')
                                ->label('Producto')
                                ->options(fn () => Product::where('store_id', Filament::getTenant()->id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (?int $state, Set $set) {
                                    if (! $state) return;
                                    $product = Product::find($state);
                                    if (! $product) return;

                                    $set('price',    $product->price_sell);
                                    $set('discount', 0);

                                    if (! $product->has_scale) {
                                        $set('quantity', 1);
                                        $set('subtotal', $product->price_sell);
                                    } else {
                                        $set('quantity', null);
                                        $set('subtotal', 0);
                                    }
                                })
                                ->required()
                                ->columnSpan(2),

                            // ── Cantidad / Peso ────────────────────────────────────
                            TextInput::make('quantity')
                                ->label(fn (Get $get) => self::qtyLabel($get('product_id')))
                                ->numeric()
                                ->step(fn (Get $get) => self::isScale($get('product_id')) ? '0.001' : '1')
                                ->suffix(fn (Get $get) => self::qtyUnit($get('product_id')))
                                ->minValue(0.001)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $price    = (float) ($get('price') ?? 0);
                                    $discount = (float) ($get('discount') ?? 0);
                                    $qty      = (float) ($state ?? 0);
                                    $set('subtotal', round(max(0, $qty * $price - $discount), 2));
                                })
                                ->required(),

                            // ── Precio unitario ────────────────────────────────────
                            TextInput::make('price')
                                ->label('Precio')
                                ->numeric()
                                ->prefix('$')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $qty      = (float) ($get('quantity') ?? 0);
                                    $discount = (float) ($get('discount') ?? 0);
                                    $set('subtotal', round(max(0, $qty * (float) $state - $discount), 2));
                                })
                                ->required(),

                            // ── Descuento por artículo ─────────────────────────────
                            TextInput::make('discount')
                                ->label('Descuento')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $qty   = (float) ($get('quantity') ?? 0);
                                    $price = (float) ($get('price') ?? 0);
                                    $set('subtotal', round(max(0, $qty * $price - (float) $state), 2));
                                }),

                            // ── Subtotal (calculado) ───────────────────────────────
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('$')
                                ->disabled()
                                ->dehydrated(),

                        ])
                        ->columns(6)
                        ->addActionLabel('+ Agregar producto manualmente')
                        ->live()
                        ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateTotal($set, $get))
                        ->reorderable(false)
                        ->cloneable(false),
                ]),

            Section::make()
                ->schema([
                    TextInput::make('total')
                        ->label('Total de la venta')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated()
                        ->default(0),
                ]),

        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function recalculateTotal(Set $set, Get $get): void
    {
        $items    = $get('items') ?? [];
        $subtotal = collect($items)->sum(fn ($i) => (float) ($i['subtotal'] ?? 0));
        $discount = (float) ($get('discount') ?? 0);
        $set('total', round(max(0, $subtotal - $discount), 2));
    }

    private static function isScale(?int $productId): bool
    {
        if (! $productId) return false;
        return (bool) Product::find($productId)?->has_scale;
    }

    private static function qtyLabel(?int $productId): string
    {
        if (! $productId) return 'Cantidad';
        $p = Product::find($productId);
        return $p?->has_scale
            ? 'Peso (' . $p->unidad . ')'
            : 'Cantidad';
    }

    private static function qtyUnit(?int $productId): string
    {
        if (! $productId) return 'pz';
        $p = Product::find($productId);
        return $p?->has_scale ? ($p->unidad) : 'pz';
    }
}

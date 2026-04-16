<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;


class LowStockProducts extends BaseWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('widget_LowStockProducts') ?? false;
    }

    // Título de la tabla
    protected static ?string $heading = '⚠️ Productos por Agotarse';

    // Lo ponemos en la posición 3 (debajo de la gráfica)
    protected static ?int $sort = 3;

    // Hacemos que ocupe todo el ancho
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Buscamos productos de esta tienda que tengan 5 o menos en stock
                Product::query()
                    ->where('store_id', filament()->getTenant()->id)
                    ->where('stock', '<=', 5)
                    ->orderBy('stock', 'asc') // Los que tienen menos salen hasta arriba
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('barcode')
                    ->label('Código de Barras')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('stock')
                    ->label('Existencia')
                    ->badge()
                    // Si el stock es 0, lo pintamos de rojo (danger), si no, de amarillo (warning)
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        default => 'warning',
                    })
                    ->size('lg'),
            ])
            ->actions([
                // Un botón rápido para ir a editar el producto y sumarle stock cuando llegue el proveedor
                Action::make('abastecer')
                    ->label('Abastecer')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', ['tenant' => filament()->getTenant()->id, 'record' => $record]))
            ])
            ->paginated([5]); // Mostramos solo los primeros 5 para que no haga el Dashboard larguísimo
    }
}
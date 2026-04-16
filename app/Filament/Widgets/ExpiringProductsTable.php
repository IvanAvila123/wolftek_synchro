<?php

namespace App\Filament\Widgets;

use App\Models\ProductBatch;
use Carbon\Carbon;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringProductsTable extends BaseWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('widget_ExpiringProductsTable') ?? false;
    }

    // Título llamativo
    protected static ?string $heading = '🚨 Alertas de Caducidad (Próximos 30 días)';

    // Lo ponemos en la posición 4 (debajo de tu widget de stock bajo)
    protected static ?int $sort = 4;

    // Que ocupe todo el ancho
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductBatch::query()
                    // Si usas multi-sucursal, asegúrate de filtrar por tienda a través de la relación product
                    ->whereHas('product', function ($query) {
                        $query->where('store_id', filament()->getTenant()->id);
                    })
                    // Solo productos que caducan en los próximos 30 días O que ya caducaron (fechas pasadas)
                    ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                    // Ordenamos: los que caducan primero (o ya caducaron) van hasta arriba
                    ->orderBy('expiry_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Piezas en Riesgo')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Fecha de Caducidad')
                    ->date('d M Y')
                    ->sortable()
                    // 👇 EL SEMÁFORO MÁGICO 👇
                    ->badge()
                    ->color(function ($state): string {
                        $fechaCaducidad = Carbon::parse($state);
                        $hoy = Carbon::today();
                        
                        // Si ya caducó (o caduca hoy), ROJO URGENTE
                        if ($fechaCaducidad->isPast() || $fechaCaducidad->isSameDay($hoy)) {
                            return 'danger';
                        }
                        
                        // Si caduca en menos de 15 días, NARANJA
                        if ($fechaCaducidad->diffInDays($hoy) <= 15) {
                            return 'warning';
                        }
                        
                        // Si caduca entre 16 y 30 días, AMARILLO/GRIS (tranquilo)
                        return 'gray';
                    })
                    ->description(function ($state) {
                         $fechaCaducidad = Carbon::parse($state);
                         $hoy = Carbon::today();
                         
                         if ($fechaCaducidad->isPast() && !$fechaCaducidad->isSameDay($hoy)) {
                             return '¡Ya caducó hace ' . $fechaCaducidad->diffInDays($hoy) . ' días!';
                         }
                         
                         if ($fechaCaducidad->isSameDay($hoy)) {
                             return '¡Caduca HOY!';
                         }
                         
                         return 'Faltan ' . $fechaCaducidad->diffInDays($hoy) . ' días';
                    }),
            ])
            ->paginated([5]); // Solo mostramos los 5 más urgentes
    }
}
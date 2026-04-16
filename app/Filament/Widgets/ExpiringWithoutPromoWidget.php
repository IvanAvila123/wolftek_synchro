<?php

namespace App\Filament\Widgets;

use App\Models\ProductBatch;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringWithoutPromoWidget extends TableWidget
{
    protected static ?string $heading = '🏷️ Lotes por Caducar sin Promoción';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('widget_ExpiringWithoutPromoWidget') ?? false;
    }

    protected function getTableQuery(): Builder
    {
        $storeId = filament()->getTenant()?->id;

        return ProductBatch::query()
            ->where('store_id', $storeId)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(60))
            // Solo lotes que NO tienen ninguna promoción activa
            ->whereDoesntHave('promotions', function ($q) {
                $q->where('activa', true);
            })
            ->whereDoesntHave('promotions', function ($q) {
                // Tampoco los que se auto-activan y ya están en rango
                $q->whereNotNull('auto_activar_dias')
                    ->whereRaw('DATEDIFF(expiry_date, NOW()) <= auto_activar_dias');
            })
            ->with('product')
            ->orderBy('expiry_date', 'asc');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return '¡Todo cubierto!';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Todos los lotes próximos a caducar ya tienen promoción.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-tag';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('quantity')
                    ->label('Uds. en lote')
                    ->badge()
                    ->color('info'),

                TextColumn::make('expiry_date')
                    ->label('Caduca en')
                    ->state(fn (ProductBatch $r) => Carbon::parse($r->expiry_date)->diffInDays(now(), false) <= 0
                        ? 'Faltan ' . Carbon::parse($r->expiry_date)->diffInDays(now(), true) . ' días'
                        : 'Hoy')
                    ->badge()
                    ->color(fn (ProductBatch $r): string => Carbon::parse($r->expiry_date)->diffInDays(now(), true) <= 15
                        ? 'danger' : 'warning'),

                TextColumn::make('expiry_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->recordUrl(fn (ProductBatch $record): string => route(
                'filament.admin.resources.product-batches.index',
                ['tenant' => filament()->getTenant()]
            ))
            ->paginated([5, 10]);
    }
}

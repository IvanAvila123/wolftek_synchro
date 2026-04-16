<?php

namespace App\Filament\Superadmin\Widgets;

use App\Models\Store;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CriticalStoresWidget extends TableWidget
{
    protected static ?string $heading = 'Tiendas que Requieren Atención';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Store::query()
            ->where(function ($q) {
                // Pago vencido o suspendidas
                $q->whereIn('estatus', ['past_due', 'unpaid', 'canceled'])
                    // O trial expirado pero sin haber convertido
                    ->orWhere(function ($q2) {
                        $q2->where('estatus', 'trial')
                            ->where('trial_ends_at', '<', now());
                    })
                    // O suscripción activa pero valid_until ya pasó
                    ->orWhere(function ($q2) {
                        $q2->where('estatus', 'active')
                            ->where('valid_until', '<', now());
                    });
            })
            ->with(['owner', 'plan'])
            ->orderByRaw("FIELD(estatus, 'past_due', 'unpaid', 'canceled', 'trial') ASC")
            ->orderBy('valid_until', 'asc');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return '¡Todo en orden!';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'No hay tiendas con problemas en este momento.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-check-circle';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Tienda')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (Store $record): string => route('filament.superadmin.resources.stores.edit', $record)),

                TextColumn::make('owner.name')
                    ->label('Dueño')
                    ->icon('heroicon-m-user'),

                TextColumn::make('business_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => Store::businessTypes()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info'),

                TextColumn::make('estatus')
                    ->label('Problema')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'past_due' => 'Pago Vencido',
                        'unpaid'   => 'Suspendida',
                        'canceled' => 'Cancelada',
                        'trial'    => 'Trial Expirado',
                        'active'   => 'Suscripción Vencida',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'past_due', 'unpaid' => 'danger',
                        'canceled'           => 'gray',
                        'trial'              => 'warning',
                        default              => 'danger',
                    }),

                TextColumn::make('trial_ends_at')
                    ->label('Fin de Trial')
                    ->date('d/m/Y')
                    ->color('danger'),

                TextColumn::make('valid_until')
                    ->label('Venció el')
                    ->date('d/m/Y')
                    ->color('danger'),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone')
                    ->copyable(),
            ])
            ->recordUrl(fn (Store $record): string => route('filament.superadmin.resources.stores.edit', $record))
            ->paginated([5, 10, 25]);
    }
}

<?php

namespace App\Filament\Cashier\Resources\Sales\Tables;

use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => '#' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cajero')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->default('sin cliente')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->weight('bold')
                    ->money('MXN', true)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Método de Pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'transfer' => 'warning',
                        'credit' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        'credit' => 'Crédito',
                        default => strtoupper($state),
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completada',
                        'pending' => 'Pendiente',
                        'canceled' => 'Cancelada',
                        default => ucfirst($state),
                    })
                    ->badge(),
            ])->defaultSort('created_at', 'desc') // Ordenar para que la venta más nueva salga hasta arriba
            ->filters([
                //
            ])
            ->recordActions([
                // 👇 ESTE ES EL BOTÓN SALVAVIDAS
                Action::make('reimprimir')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    // Usamos la misma ruta que creamos hace rato
                    ->url(fn (\App\Models\Sale $record): string => route('ticket.imprimir', $record))
                    ->openUrlInNewTab(), // Lo abre en otra pestaña para no sacar al cajero del sistema
            ])
            ->bulkActions([
                //
            ]);
    }
}

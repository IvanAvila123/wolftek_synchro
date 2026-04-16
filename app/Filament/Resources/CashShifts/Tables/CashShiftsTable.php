<?php

namespace App\Filament\Resources\CashShifts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashRegister.name')
                    ->label('Caja')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cajero')
                    ->copyable()
                    ->sortable(),
                TextColumn::make('opening_amount')
                    ->label('Monto de apertura')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('closing_amount')
                    ->label('Monto de cierre')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'open' => 'success',
                        'closed' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state){
                        'open' => 'Abierto (En turno)',
                        'closed' => 'Cerrado',
                        default => strtoupper($state),
                    }),
                TextColumn::make('opened_at')
                    ->label('Abierto el')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Cerrado el')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime('d/m/Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('opened_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierto',
                        'closed' => 'Cerrado',
                    ])
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('forzar_cierre')
                    ->label('Forzar Cierre')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Forzar cierre de esta caja?')
                    ->modalDescription('Esto cerrará el turno del cajero inmediatamente. El cajero no podrá seguir cobrando hasta que abra un nuevo turno.')
                    ->modalSubmitActionLabel('Sí, cerrar caja')
                    ->visible(fn (\App\Models\CashShift $record) => $record->status === 'open') // Solo aparece si está abierta
                    ->action(function (\App\Models\CashShift $record) {
                        
                        // Calculamos cuánto efectivo debería haber sumando ventas y abonos
                        $ventasEfectivo = \App\Models\Sale::where('cash_shift_id', $record->id)->where('payment_method', 'cash')->sum('total');
                        $abonosEfectivo = \App\Models\CreditPayment::where('cash_shift_id', $record->id)->where('payment_method', 'cash')->sum('amount');
                        
                        $esperado = $record->opening_amount + $ventasEfectivo + $abonosEfectivo;

                        // Cerramos el turno guardando el dinero esperado (ya que el gerente no está contando los billetes físicamente)
                        $record->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                            'closing_amount' => $esperado, // Asumimos que la caja cuadra perfectamente
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Caja cerrada a la fuerza')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

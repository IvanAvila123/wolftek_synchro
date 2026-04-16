<?php

namespace App\Filament\Cashier\Resources\Expenses\Tables;

use App\Models\Expense;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
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
                TextColumn::make('cashShift.status')
                ->label('Estatus')
                ->badge()
                ->color(fn (string $state): string => match ($state){
                    'open' => 'success',
                    'closed' => 'danger'
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'open' => 'Abierto',
                    'closed' => 'Cerrado',
                    default => strtoupper($state),
                }),
                TextColumn::make('user.name')
                ->label('Cajero')
                ->sortable(),
                TextColumn::make('concept')
                ->label('Concepto')
                ->sortable(),
                TextColumn::make('amount')
                ->label('Monto')
                ->weight('bold')
                ->money('MXN', true)
                ->sortable()

            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('imprimir')
                    ->label('Reimprimir')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Expense $record): string => route('ticket.gasto', $record->id))
                    ->openUrlInNewTab()
            ])->paginated([5])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

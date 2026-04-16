<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Folio')
                    ->sortable(),
                TextColumn::make('store.name')
                    ->label('Tienda')
                    ->sortable(),
                TextColumn::make('supplier.company')
                    ->label('Proveedor')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('MXN')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('estatus')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pagada' => 'success',
                        'pendiente' => 'warning',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('usuario')
                    ->sortable(),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                    ->label('Ver Detalles'),
                    EditAction::make()
                    ->label('Editar'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

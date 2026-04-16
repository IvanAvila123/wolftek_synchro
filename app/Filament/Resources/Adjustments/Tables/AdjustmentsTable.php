<?php

namespace App\Filament\Resources\Adjustments\Tables;

use Dom\Text;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Cant.')
                    ->badge()
                    ->color('danger')
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Motivo')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('user.name')
                    ->label('Reportado por')
                    ->searchable()
                    ->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

}

<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Models\Promotion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nombre')
                    ->label('Promoción')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => Promotion::tipoLabel($state))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'porcentaje'  => 'info',
                        'precio_fijo' => 'success',
                        'nxm'         => 'primary',
                        default       => 'gray',
                    }),

                TextColumn::make('detalle')
                    ->label('Descuento')
                    ->state(fn (Promotion $record) => match ($record->tipo) {
                        'porcentaje'  => "{$record->valor}% off",
                        'precio_fijo' => "\${$record->valor} c/u",
                        'nxm'         => "Llevas {$record->cantidad_lleva}, pagas {$record->cantidad_paga}",
                        default       => '—',
                    }),

                TextColumn::make('batch.expiry_date')
                    ->label('Lote vence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Promotion $record) => $record->batch?->expiry_date?->lt(now())
                        ? 'danger' : 'warning'),

                TextColumn::make('auto_activar_dias')
                    ->label('Auto (días)')
                    ->placeholder('—')
                    ->badge()
                    ->color('info')
                    ->suffix(' días'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (Promotion $record) => $record->estaVigente() ? 'Activa' : 'Inactiva')
                    ->badge()
                    ->color(fn (Promotion $record) => $record->estaVigente() ? 'success' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'porcentaje'  => '% Descuento',
                        'precio_fijo' => 'Precio Especial',
                        'nxm'         => 'NxM',
                    ]),

                TernaryFilter::make('activa')
                    ->label('Activa manualmente')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
                    ->placeholder('Todas'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Editar'),

                    Action::make('toggle')
                        ->label(fn (Promotion $r) => $r->activa ? 'Desactivar' : 'Activar')
                        ->icon(fn (Promotion $r) => $r->activa ? 'heroicon-m-pause' : 'heroicon-m-play')
                        ->color(fn (Promotion $r) => $r->activa ? 'warning' : 'success')
                        ->action(function (Promotion $record) {
                            $record->update(['activa' => ! $record->activa]);
                            Notification::make()
                                ->success()
                                ->title($record->fresh()->activa ? 'Promoción activada' : 'Promoción desactivada')
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}

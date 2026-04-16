<?php

namespace App\Filament\Superadmin\Resources\Subscriptions\Tables;

use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn (Subscription $record) => route(
                        'filament.superadmin.resources.stores.edit',
                        $record->store_id
                    )),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->label('Estatus')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'activo'     => 'Activo',
                        'suspendido' => 'Suspendido',
                        'cancelado'  => 'Cancelado',
                        default      => $state,
                    })
                    ->color(fn (Subscription $record) => match ($record->estatus) {
                        'activo'     => 'success',
                        'suspendido' => 'warning',
                        'cancelado'  => 'danger',
                        default      => 'gray',
                    }),

                TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'tarjeta'  => 'Tarjeta',
                        'spei'     => 'SPEI',
                        'oxxo'     => 'OXXO',
                        'efectivo' => 'Efectivo',
                        default    => $state,
                    })
                    ->color('gray'),

                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Subscription $record) => $record->ends_at && $record->ends_at < now() ? 'danger' : 'success'),

                TextColumn::make('conekta_subscription_id')
                    ->label('ID Conekta')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registrada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estatus')
                    ->label('Estatus')
                    ->options([
                        'activo'     => 'Activo',
                        'suspendido' => 'Suspendido',
                        'cancelado'  => 'Cancelado',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'tarjeta'  => 'Tarjeta',
                        'spei'     => 'SPEI',
                        'oxxo'     => 'OXXO',
                        'efectivo' => 'Efectivo',
                    ]),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Editar')->icon('heroicon-m-pencil'),

                    Action::make('renovar')
                        ->label('Renovar 30 días')
                        ->icon('heroicon-m-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Subscription $record) {
                            $base = $record->ends_at && $record->ends_at > now()
                                ? $record->ends_at
                                : now();

                            $record->update([
                                'ends_at' => $base->copy()->addDays(30),
                                'estatus' => 'activo',
                            ]);

                            // Sincronizar also la tienda
                            $record->store->update([
                                'valid_until' => $base->copy()->addDays(30),
                                'estatus'     => 'active',
                                'is_active'   => true,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Suscripción renovada')
                                ->body("Acceso extendido hasta {$record->fresh()->ends_at->format('d/m/Y')}.")
                                ->send();
                        }),

                    Action::make('cancelar')
                        ->label('Cancelar Suscripción')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Esto cancelará la suscripción y suspenderá la tienda.')
                        ->visible(fn (Subscription $record) => $record->estatus !== 'cancelado')
                        ->action(function (Subscription $record) {
                            $record->update(['estatus' => 'cancelado']);
                            $record->store->update([
                                'estatus'   => 'canceled',
                                'is_active' => false,
                            ]);
                            Notification::make()
                                ->warning()
                                ->title('Suscripción cancelada')
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

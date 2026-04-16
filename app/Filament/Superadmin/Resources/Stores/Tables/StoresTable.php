<?php

namespace App\Filament\Superadmin\Resources\Stores\Tables;

use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Store $record) => Store::businessTypes()[$record->business_type] ?? '—'),

                TextColumn::make('owner.name')
                    ->label('Dueño')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),

                TextColumn::make('business_type')
                    ->label('Tipo de Negocio')
                    ->formatStateUsing(fn ($state) => Store::businessTypes()[$state] ?? $state)
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-rectangle-stack')
                    ->sortable(),

                TextColumn::make('trial_ends_at')
                    ->label('Fin de Prueba')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Store $record) => $record->trial_ends_at && $record->trial_ends_at < now() ? 'danger' : 'success'),

                TextColumn::make('valid_until')
                    ->label('Vencimiento')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Store $record) => $record->valid_until && $record->valid_until < now() ? 'danger' : 'success'),

                ToggleColumn::make('is_active')
                    ->label('Activa')
                    ->sortable(),

                TextColumn::make('estatus')
                    ->label('Estatus')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'trial'    => 'En Prueba',
                        'active'   => 'Activa',
                        'past_due' => 'Pago Vencido',
                        'unpaid'   => 'Suspendida',
                        'canceled' => 'Cancelada',
                        default    => $state,
                    })
                    ->color(fn (Store $record) => match ($record->estatus) {
                        'trial'    => 'warning',
                        'active'   => 'success',
                        'past_due', 'unpaid', 'canceled' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estatus')
                    ->label('Estatus')
                    ->options([
                        'trial'    => 'En Prueba',
                        'active'   => 'Activa',
                        'past_due' => 'Pago Vencido',
                        'unpaid'   => 'Suspendida',
                        'canceled' => 'Cancelada',
                    ]),

                SelectFilter::make('business_type')
                    ->label('Tipo de Negocio')
                    ->options(Store::businessTypes()),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas')
                    ->placeholder('Todas'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver')
                        ->icon('heroicon-m-eye'),
                    EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-m-pencil'),

                    Action::make('activar')
                        ->label('Activar')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('¿Activar tienda?')
                        ->modalDescription(fn (Store $record) => "Se activará \"{$record->name}\" y podrá acceder al sistema.")
                        ->visible(fn (Store $record) => ! $record->is_active || $record->estatus !== 'active')
                        ->action(function (Store $record) {
                            $record->update([
                                'is_active' => true,
                                'estatus'   => 'active',
                            ]);
                            Notification::make()
                                ->success()
                                ->title('Tienda activada')
                                ->body("{$record->name} está activa ahora.")
                                ->send();
                        }),

                    Action::make('suspender')
                        ->label('Suspender')
                        ->icon('heroicon-m-pause-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Suspender tienda?')
                        ->modalDescription(fn (Store $record) => "Se bloqueará el acceso a \"{$record->name}\" hasta que se regularice el pago.")
                        ->visible(fn (Store $record) => $record->is_active)
                        ->action(function (Store $record) {
                            $record->update([
                                'is_active' => false,
                                'estatus'   => 'unpaid',
                            ]);
                            Notification::make()
                                ->warning()
                                ->title('Tienda suspendida')
                                ->body("{$record->name} ha sido bloqueada.")
                                ->send();
                        }),

                    Action::make('renovar')
                        ->label('Renovar 30 días')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('¿Renovar suscripción?')
                        ->modalDescription(fn (Store $record) => "Se extenderá 30 días a partir de hoy para \"{$record->name}\".")
                        ->action(function (Store $record) {
                            $base = $record->valid_until && $record->valid_until > now()
                                ? $record->valid_until
                                : now();

                            $record->update([
                                'valid_until' => $base->addDays(30),
                                'estatus'     => 'active',
                                'is_active'   => true,
                            ]);
                            Notification::make()
                                ->success()
                                ->title('Suscripción renovada')
                                ->body("{$record->name} tiene acceso hasta {$record->fresh()->valid_until->format('d/m/Y')}.")
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('activar_seleccionadas')
                        ->label('Activar seleccionadas')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true, 'estatus' => 'active']);
                            Notification::make()->success()->title('Tiendas activadas')->send();
                        }),

                    Action::make('suspender_seleccionadas')
                        ->label('Suspender seleccionadas')
                        ->icon('heroicon-m-pause-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false, 'estatus' => 'unpaid']);
                            Notification::make()->warning()->title('Tiendas suspendidas')->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

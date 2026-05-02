<?php

namespace App\Filament\Superadmin\Resources\SubscriptionPayments;

use App\Filament\Superadmin\Resources\SubscriptionPayments\Pages\ListSubscriptionPayments;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SubscriptionPaymentResource extends Resource
{
    protected static ?string $model = SubscriptionPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $modelLabel = 'Pago por Transferencia';

    protected static ?string $pluralModelLabel = 'Pagos por Transferencia';

    protected static string|\UnitEnum|null $navigationGroup = 'SaaS';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $pending = SubscriptionPayment::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pagos pendientes de aprobación';
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Tienda')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (SubscriptionPayment $r) => $r->store->owner?->email),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'transfer'    => '🏦 Transferencia',
                        'mercadopago' => '💳 MercadoPago',
                        default       => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'transfer'    => 'secondary',
                        'mercadopago' => 'info',
                        default       => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'  => 'En revisión',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->copyable()
                    ->placeholder('—')
                    ->fontFamily('mono'),

                TextColumn::make('notes')
                    ->label('Notas del cliente')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('admin_notes')
                    ->label('Notas admin')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('Aprobado el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Solicitado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending'  => 'En revisión',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ])
                    ->default('pending'),

                SelectFilter::make('payment_method')
                    ->label('Método')
                    ->options([
                        'transfer'    => 'Transferencia',
                        'mercadopago' => 'MercadoPago',
                    ]),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('aprobar')
                        ->label('Aprobar pago')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn (SubscriptionPayment $r) => $r->status === 'pending')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar pago por transferencia')
                        ->modalDescription(fn (SubscriptionPayment $r) =>
                            "Se extenderá la suscripción de {$r->store->name} al plan {$r->plan->name} por {$r->months} mes(es)."
                        )
                        ->modalSubmitActionLabel('Sí, aprobar y activar')
                        ->action(function (SubscriptionPayment $record) {
                            DB::transaction(function () use ($record) {
                                $store = $record->store;
                                $plan  = $record->plan;

                                $base     = $store->valid_until && Carbon::parse($store->valid_until)->gt(now())
                                    ? Carbon::parse($store->valid_until)
                                    : now();
                                $newValid = $base->copy()->addMonths($record->months);

                                $store->update([
                                    'plan_id'     => $plan->id,
                                    'estatus'     => 'active',
                                    'is_active'   => true,
                                    'valid_until' => $newValid,
                                ]);

                                Subscription::create([
                                    'store_id'       => $store->id,
                                    'plan_id'        => $plan->id,
                                    'estatus'        => 'activo',
                                    'payment_method' => 'transfer',
                                    'starts_at'      => now(),
                                    'ends_at'        => $newValid,
                                ]);

                                $record->update([
                                    'status'      => 'approved',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);

                                // Notificar al dueño de la tienda
                                Notification::make()
                                    ->title('¡Suscripción activada!')
                                    ->body("Tu pago fue aprobado. El plan {$plan->name} está activo hasta el {$newValid->format('d/m/Y')}.")
                                    ->success()
                                    ->sendToDatabase($store->owner);
                            });

                            Notification::make()
                                ->title('Pago aprobado')
                                ->body('La suscripción se activó automáticamente.')
                                ->success()
                                ->send();
                        }),

                    Action::make('rechazar')
                        ->label('Rechazar pago')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn (SubscriptionPayment $r) => $r->status === 'pending')
                        ->form([
                            Textarea::make('admin_notes')
                                ->label('Motivo del rechazo')
                                ->placeholder('Ej. No se encontró el movimiento en el estado de cuenta, monto incorrecto...')
                                ->required()
                                ->rows(3),
                        ])
                        ->modalHeading('Rechazar pago')
                        ->modalSubmitActionLabel('Rechazar')
                        ->action(function (SubscriptionPayment $record, array $data) {
                            $record->update([
                                'status'      => 'rejected',
                                'admin_notes' => $data['admin_notes'],
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Pago rechazado')
                                ->body("Motivo: {$data['admin_notes']}")
                                ->danger()
                                ->sendToDatabase($record->store->owner);

                            Notification::make()
                                ->title('Pago rechazado')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPayments::route('/'),
        ];
    }
}

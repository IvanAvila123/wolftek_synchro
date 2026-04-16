<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Tienda')
                    ->sortable(),
                TextColumn::make('company')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Contacto')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('balance')
                    ->label('Deuda Actual')
                    ->money('MXN')
                    ->sortable()
                    ->color(fn($state): string => $state > 0 ? 'danger' : 'gray')
                    ->weight('bold'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver'),
                    EditAction::make()
                        ->label('Editar'),
                    DeleteAction::make()
                        ->label('Eliminar'),
                    Action::make('abonar')
                        ->label('Abonar')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(fn(\App\Models\Supplier $record): bool => $record->balance > 0)
                        ->form([
                            TextInput::make('monto')
                                ->label('¿Cuánto le vas a pagar a este proveedor?')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn(\App\Models\Supplier $record) => $record->balance)
                                ->helperText(fn(\App\Models\Supplier $record) => 'El monto máximo que puedes abonar es: $' . number_format($record->balance, 2) )

                        ])
                        ->modalHeading(fn (\App\Models\Supplier $record) => 'Registrar Pago a ' . $record->company)
                        ->modalWidth('sm')
                        ->action(function (array $data, \App\Models\Supplier $record) {
                        // Le restamos el monto pagado a la deuda actual
                        $record->decrement('balance', $data['monto']);

                        // Le avisamos al usuario que todo salió bien
                        \Filament\Notifications\Notification::make()
                            ->title('Pago registrado correctamente')
                            ->success()
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

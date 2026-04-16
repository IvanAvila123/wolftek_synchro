<?php

namespace App\Filament\Superadmin\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('role_name')
                    ->label('Rol')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->getDirectRoleName())
                    ->color(fn(?string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'owner' => 'success',
                        'manager' => 'warning',
                        'cashier' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => ucfirst($state ?? 'Sin rol')),

                TextColumn::make('store_name')
                    ->label('Tienda')
                    ->getStateUsing(function ($record) {
                        return $record->store?->name
                            ?? $record->employee?->store?->name
                            ?? 'Sin tienda';
                    })
                    ->searchable(false),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(
                        \Spatie\Permission\Models\Role::pluck('name', 'name')
                            ->map(fn($name) => ucfirst($name))
                    )
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas(
                                'roles',
                                fn($q) => $q
                                    ->where('name', $data['value'])
                                    ->withoutGlobalScopes()
                            );
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('resetPassword')
                        ->label('Reset')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->modalHeading('Restablecer contraseña')
                        ->modalDescription(fn($record) => "Cambiar contraseña de {$record->name}")
                        ->form([
                            TextInput::make('new_password')
                                ->label('Nueva contraseña')
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->required(),
                            TextInput::make('new_password_confirmation')
                                ->label('Confirmar contraseña')
                                ->password()
                                ->revealable()
                                ->same('new_password')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'password' => bcrypt($data['new_password']),
                            ]);
                            Notification::make()
                                ->title('Contraseña actualizada')
                                ->body("La contraseña de {$record->name} fue cambiada exitosamente.")
                                ->success()
                                ->send();
                        }),

                        Action::make('toggleActive')
                            ->label(fn($record) => $record->is_active ? 'Desactivar' : 'Activar')
                            ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                            ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                            ->action(function ($record) {
                                $record->update(['is_active' => !$record->is_active]);
                                Notification::make()
                                    ->title('Estado actualizado')
                                    ->body("El usuario {$record->name} ahora está " . ($record->is_active ? 'activo' : 'inactivo') . '.')
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

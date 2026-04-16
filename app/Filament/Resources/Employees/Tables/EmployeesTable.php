<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user'),

                TextColumn::make('user.email')
                    ->label('Correo')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->color('gray'),

                TextColumn::make('user.phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->placeholder('Sin teléfono')
                    ->icon('heroicon-o-phone'),

                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manager' => 'warning',
                        'cashier' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                IconColumn::make('user.is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(
                        \Spatie\Permission\Models\Role::whereNotIn('name', ['super_admin', 'owner'])
                            ->whereNull('store_id')
                            ->pluck('name', 'name')
                            ->map(fn ($name) => ucfirst($name))
                    )
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->whereHas('user.roles', fn ($q) => $q->where('name', $data['value']));
                        }
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    
                ViewAction::make(),
                EditAction::make(),
                Action::make('cambiarPassword')
                    ->label('Cambiar contraseña')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    
                    ->modalHeading(fn ($record) => 'Cambiar contraseña — ' . $record->user->name)
                    ->modalDescription(fn ($record) => $record->user->email)
                    ->modalIcon('heroicon-o-key')
                    ->form([
                        TextInput::make('new_password')
                            ->label('Nueva contraseña')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8),
                        TextInput::make('new_password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->same('new_password')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->user->update([
                            'password' => bcrypt($data['new_password']),
                        ]);
                        Notification::make()
                            ->title('Contraseña actualizada')
                            ->body("La contraseña de {$record->user->name} fue cambiada exitosamente.")
                            ->success()
                            ->send();
                    }),
                ])
                
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin empleados')
            ->emptyStateDescription('Crea tu primer empleado para comenzar a gestionar tu equipo.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->striped();
    }
}
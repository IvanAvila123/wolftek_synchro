<?php

namespace App\Filament\Superadmin\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),

                TextEntry::make('email')
                    ->label('Correo electrónico'),

                TextEntry::make('phone')
                    ->label('Teléfono')
                    ->placeholder('Sin teléfono'),

                TextEntry::make('role_name')
                    ->label('Rol')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->getDirectRoleName())
                    ->color(fn(?string $state) => match ($state) {
                        'super_admin' => 'danger',
                        'owner' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => ucfirst($state ?? 'Sin rol')),

                IconEntry::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextEntry::make('store.name')
                    ->label('Tienda')
                    ->placeholder('Sin tienda'),

                TextEntry::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}

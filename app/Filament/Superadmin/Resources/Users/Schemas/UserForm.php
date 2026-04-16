<?php

namespace App\Filament\Superadmin\Resources\Users\Schemas;

use Dom\Text;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Factories\Relationship;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre completo')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->visibleOn(Operation::Create)
                    ->minLength(8)
                    ->password()
                    ->required(),
                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->visibleOn(Operation::Create)
                    ->minLength(8)
                    ->password()
                    ->dehydrated(false)
                    ->required(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->nullable()
                    ->tel(),
                Select::make('role')
                    ->label('Rol')
                    ->options(
                        \App\Models\Role::whereNull('store_id')
                            ->pluck('name', 'name')
                            ->map(fn($name) => ucfirst($name))
                    )
                    ->afterStateHydrated(
                        fn($component, $record) =>
                        $record ? $component->state($record->getDirectRoleName()) : null
                    )
                    ->placeholder('Sin rol (se asignará al registrar tienda)')
                    ->nullable(),
                Toggle::make('is_active')
                    ->label('Usuario activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}

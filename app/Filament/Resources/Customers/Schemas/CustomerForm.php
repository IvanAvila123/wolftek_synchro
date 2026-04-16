<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Cliente')
                    ->label('Información del Cliente')
                    ->description('Complete la información del cliente.')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('name', ucwords($state)))
                            ->required(),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel(),
                        TextInput::make('credit_limit')
                            ->label('Límite de Crédito')
                            ->helperText('Pon 0 para crédito ilimitado')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),

                    ])
                    ->columns(2),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor')
                    ->description('Datos generales y de contacto de la empresa que nos surte.')
                    ->icon('heroicon-o-truck')
                    ->columns(2)
                    ->schema([
                        TextInput::make('company')
                            ->label('Empresa / Marca')
                            ->placeholder('Ej. Coca-Cola, Sabritas, Bimbo...')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('company', ucwords($state)))
                            ->maxLength(255),

                        TextInput::make('name')
                            ->label('Nombre del Repartidor / Contacto')
                            ->placeholder('Ej. Juan Pérez')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('name', ucwords($state)))
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo Electronico')
                            ->default('Sin Correo')
                            ->maxLength(255),

                    ]),

                Section::make('Crédito y Saldos')
                    ->description('Configuración financiera con este proveedor.')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('credit_limit')
                            ->label('Límite de Crédito')
                            ->helperText('¿Cuánto nos fía este proveedor como máximo?')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('balance')
                            ->label('Saldo Pendiente (Nuestra Deuda)')
                            ->helperText('El sistema calculará esto automáticamente con las compras y pagos.')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled() // Lo bloqueamos para que no lo editen a mano por error
                            ->dehydrated(false)
                    ])
            ]);
    }
}

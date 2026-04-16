<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del empleado')
                    ->description('Detalles personales del empleado')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Nombre completo'),

                        TextEntry::make('user.email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('user.phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('Sin teléfono'),

                        TextEntry::make('role')
                            ->label('Rol')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'manager' => 'warning',
                                'cashier' => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        IconEntry::make('user.is_active')
                            ->label('Estado')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle'),

                        TextEntry::make('created_at')
                            ->label('Fecha de registro')
                            ->dateTime('d/m/Y H:i'),
                    ]),

                Section::make('Datos Fiscales')
                    ->description('Información fiscal del empleado')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('success')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rfc')
                            ->label('RFC')
                            ->placeholder('Sin RFC'),

                        TextEntry::make('curp')
                            ->label('CURP')
                            ->placeholder('Sin CURP'),
                    ]),
            ]);
    }
}
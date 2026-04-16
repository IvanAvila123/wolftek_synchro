<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Cliente')
                    ->label('Información del Cliente')
                    ->description('Detalles del cliente.')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->schema([
                        TextEntry::make('store.name')
                            ->label('Tienda'),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('phone')
                            ->label('Teléfono'),
                        TextEntry::make('credit_limit')
                            ->label('Límite de Crédito')
                            ->numeric()
                            ->prefix('$'),
                        TextEntry::make('balance')
                            ->label('Saldo')
                            ->numeric()
                            ->prefix('$'),
                        TextEntry::make('created_at')
                            ->label('Creado en')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Actualizado en')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}

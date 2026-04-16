<?php

namespace App\Filament\Resources\CashRegisters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashRegisterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('informacion de la caja')
                    ->description('Detalles de la caja registradora')
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre de la Caja')
                            ->weight('bold'),
                        TextEntry::make('created_at')
                            ->label('Fecha de creación')
                            ->dateTime('d/m/Y')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/m/Y')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}

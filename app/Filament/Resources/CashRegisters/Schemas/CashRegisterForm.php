<?php

namespace App\Filament\Resources\CashRegisters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashRegisterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la Caja')
                    ->placeholder('Ej: Caja Principal')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
<?php

namespace App\Filament\Resources\CashShifts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cash_register_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('opening_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('closing_amount')
                    ->numeric(),
                Select::make('status')
                    ->options(['open' => 'Open', 'closed' => 'Closed'])
                    ->default('open')
                    ->required(),
                DateTimePicker::make('opened_at')
                    ->required(),
                DateTimePicker::make('closed_at'),
            ]);
    }
}

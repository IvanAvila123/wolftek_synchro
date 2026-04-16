<?php

namespace App\Filament\Resources\Adjustments\Schemas;

use Dom\Text;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Merma')
                    ->description('Registra el producto que se perdió y el motivo.')
                    ->icon('heroicon-o-information-circle')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('type')
                            ->label('Motivo de la baja')
                            ->options([
                                'caducado' => 'Caducado / Vencido',
                                'dañado' => 'Dañado / Roto',
                                'robo' => 'Robo / Extravío',
                                'consumo' => 'Consumo interno (Dueño/Empleados)',
                                'otro' => 'Otro',
                            ])
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Cantidad a descontar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('notes')
                            ->label('Notas / Justificación')
                            ->placeholder('Ej. Se rompió al acomodar el exhibidor')
                            ->maxLength(255),
                        Hidden::make('user_id')
                            ->default(fn() => auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }
}

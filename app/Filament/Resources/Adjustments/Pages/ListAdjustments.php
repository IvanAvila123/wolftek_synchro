<?php

namespace App\Filament\Resources\Adjustments\Pages;

use App\Filament\Resources\Adjustments\AdjustmentResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListAdjustments extends ListRecords
{
    protected static string $resource = AdjustmentResource::class;

    public function getTitle(): string
    {
        return 'Mermas y Devoluciones';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create')
                ->label('Reportar Merma')
                ->icon('heroicon-o-minus-circle')
                // Le decimos a Filament que use modal en lugar de página nueva
                ->slideOver() // Puedes cambiar esto a ->modalWidth('md') si prefieres un cuadro al centro en lugar de que salga de lado
                ->schema([
                    Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
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
                // Mudamos aquí nuestra lógica de inventario que teníamos en el CreateAdjustment.php
                ->after(function ($record) { 
                    $producto = Product::find($record->product_id);
                    if ($producto) {
                        $producto->decrement('stock', $record->quantity);
                    }
                })
                ->extraModalFooterActions(fn(Action $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => false]),
                ])
        ];
    }
}

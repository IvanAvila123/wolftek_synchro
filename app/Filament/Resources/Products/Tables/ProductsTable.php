<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->barcode ? "Código: {$record->barcode}" : null),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price_sell')
                    ->label('Precio venta')
                    ->money('MXN')
                    ->sortable(),

                TextColumn::make('price_buy')
                    ->label('Precio compra')
                    ->money('MXN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($record): string =>
                        $record->stock <= $record->stock_min ? 'danger' : 'success'
                    )
                    ->weight(fn ($record): ?string =>
                        $record->stock <= $record->stock_min ? 'bold' : null
                    )
                    ->icon(fn ($record): ?string =>
                        $record->stock <= $record->stock_min ? 'heroicon-o-exclamation-triangle' : null
                    ),

                TextColumn::make('unidad')
                    ->label('Unidad')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pieza' => 'Pieza',
                        'kg' => 'Kg',
                        'litro' => 'Litro',
                        'gramo' => 'Gramo',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('unidad')
                    ->label('Unidad')
                    ->options([
                        'pieza' => 'Pieza',
                        'kg' => 'Kilogramo',
                        'litro' => 'Litro',
                        'gramo' => 'Gramo',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),

                TernaryFilter::make('stock_bajo')
                    ->label('Stock bajo')
                    ->queries(
                        true: fn ($query) => $query->whereColumn('stock', '<=', 'stock_min'),
                        false: fn ($query) => $query->whereColumn('stock', '>', 'stock_min'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('imprimir_varias')
                ->label('Etiquetas')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->form([
                    TextInput::make('cantidad')
                    ->label('¿Cuántas etiquetas necesitas imprimir?')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(100)
                    ->required(),
                ])
                ->modalHeading('Generar Etiquetas')
                ->modalWidth('sm')
                ->action(function (Product $record, array $data){
                    $cantidad =(int) $data['cantidad'];

                    $idsArray = array_fill(0, $cantidad, $record->id);
                    $ids = implode(',' , $idsArray);

                    Notification::make()
                    ->title("¡$cantidad etiquetas listas!")
                    ->success()
                    ->actions([
                        Action::make('imprimir')
                        ->label('🖨️ Abrir Etiquetas')
                        ->button()
                        ->color('success')
                        ->url(route('etiquetas.imprimir', ['ids' => $ids]), shouldOpenInNewTab:true),
                    ])
                    ->send();
                })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin productos')
            ->emptyStateDescription('Agrega tu primer producto para comenzar a vender.')
            ->emptyStateIcon('heroicon-o-cube')
            ->striped();
    }
}
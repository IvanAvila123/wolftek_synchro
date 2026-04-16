<?php

namespace App\Filament\Resources\ProductBatches\Tables;

use App\Models\ProductBatch;
use App\Models\Promotion;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('quantity')
                    ->label('Piezas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->label('Fecha de Caducidad')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(function ($state): string {
                        $fecha = Carbon::parse($state)->startOfDay();
                        $hoy   = Carbon::today();
                        if ($fecha->lte($hoy))                          return 'danger';
                        if ($fecha->diffInDays($hoy, true) <= 15)       return 'warning';
                        return 'gray';
                    })
                    ->description(function ($state) {
                        $fecha = Carbon::parse($state)->startOfDay();
                        $hoy   = Carbon::today();
                        if ($fecha->lt($hoy))  return '¡Ya caducó hace ' . $fecha->diffInDays($hoy, true) . ' días!';
                        if ($fecha->isToday()) return '¡Caduca HOY!';
                        return 'Faltan ' . $fecha->diffInDays($hoy, true) . ' días';
                    }),

                // Indicador de promoción activa
                TextColumn::make('promo')
                    ->label('Promoción')
                    ->state(function (ProductBatch $record): string {
                        $promo = $record->load('promotions')->promocionActiva();
                        return $promo ? $promo->nombre : '—';
                    })
                    ->badge()
                    ->color(fn ($state) => $state === '—' ? 'gray' : 'success'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expiry_date', 'asc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('crear_promo')
                        ->label('Crear Promoción')
                        ->icon('heroicon-m-tag')
                        ->color('warning')
                        ->modalHeading(fn (ProductBatch $record) => "Promoción para {$record->product->name}")
                        ->modalDescription(fn (ProductBatch $record) => "Lote de {$record->quantity} uds. — vence {$record->expiry_date?->format('d/m/Y')}")
                        ->form([
                            TextInput::make('nombre')
                                ->label('Nombre de la Promoción')
                                ->placeholder('Ej: Aspirina 3x2, 20% OFF caducidad')
                                ->required()
                                ->maxLength(100),

                            Select::make('tipo')
                                ->label('Tipo de Descuento')
                                ->options([
                                    'porcentaje'  => '% Descuento',
                                    'precio_fijo' => 'Precio Especial ($)',
                                    'nxm'         => 'NxM  (ej: 3x2, lleva más paga menos)',
                                ])
                                ->required()
                                ->native(false)
                                ->live(),

                            TextInput::make('valor')
                                ->label(fn ($get) => $get('tipo') === 'precio_fijo' ? 'Precio especial ($)' : 'Porcentaje (%)')
                                ->numeric()->minValue(0.01)
                                ->visible(fn ($get) => in_array($get('tipo'), ['porcentaje', 'precio_fijo']))
                                ->required(fn ($get) => in_array($get('tipo'), ['porcentaje', 'precio_fijo'])),

                            TextInput::make('cantidad_paga')
                                ->label('Paga (cuánto cobra el cliente)')
                                ->helperText('En "3x2": el cliente paga 2')
                                ->numeric()->integer()->minValue(1)->placeholder('2')
                                ->visible(fn ($get) => $get('tipo') === 'nxm')
                                ->required(fn ($get) => $get('tipo') === 'nxm'),

                            TextInput::make('cantidad_lleva')
                                ->label('Lleva (cuánto recibe el cliente)')
                                ->helperText('En "3x2": el cliente lleva 3')
                                ->numeric()->integer()->minValue(2)->placeholder('3')
                                ->visible(fn ($get) => $get('tipo') === 'nxm')
                                ->required(fn ($get) => $get('tipo') === 'nxm'),

                            TextInput::make('auto_activar_dias')
                                ->label('Auto-activar cuando queden ≤ X días')
                                ->numeric()->integer()->minValue(1)->suffix('días')
                                ->placeholder('30')
                                ->helperText('Déjalo vacío para activarla manualmente.'),

                            Toggle::make('activa')
                                ->label('Activar ahora')
                                ->default(true)
                                ->onColor('success'),

                            DatePicker::make('ends_at')
                                ->label('Válida hasta')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->helperText('Por defecto es la fecha de caducidad del lote.'),
                        ])
                        ->action(function (ProductBatch $record, array $data) {
                            Promotion::create([
                                'store_id'         => Filament::getTenant()->id,
                                'product_batch_id' => $record->id,
                                'product_id'       => $record->product_id,
                                'nombre'           => $data['nombre'],
                                'tipo'             => $data['tipo'],
                                'valor'            => $data['valor'] ?? null,
                                'cantidad_paga'    => $data['cantidad_paga'] ?? null,
                                'cantidad_lleva'   => $data['cantidad_lleva'] ?? null,
                                'auto_activar_dias'=> $data['auto_activar_dias'] ?? null,
                                'activa'           => $data['activa'],
                                'ends_at'          => $data['ends_at'] ?? $record->expiry_date,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('¡Promoción creada!')
                                ->body("La promoción \"{$data['nombre']}\" está lista.")
                                ->send();
                        }),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}

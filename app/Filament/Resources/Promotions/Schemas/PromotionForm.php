<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Models\ProductBatch;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Lote a Promocionar')
                ->icon('heroicon-o-cube')
                ->schema([
                    Select::make('product_batch_id')
                        ->label('Lote')
                        ->options(function () {
                            $storeId = Filament::getTenant()?->id;
                            return ProductBatch::where('store_id', $storeId)
                                ->with('product')
                                ->whereNotNull('expiry_date')
                                ->where('quantity', '>', 0)
                                ->orderBy('expiry_date')
                                ->get()
                                ->mapWithKeys(fn ($b) => [
                                    $b->id => "{$b->product->name} — vence {$b->expiry_date->format('d/m/Y')} ({$b->quantity} uds.)",
                                ]);
                        })
                        ->required()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) return;
                            $batch = ProductBatch::with('product')->find($state);
                            if ($batch) {
                                $set('product_id', $batch->product_id);
                                $set('store_id',   $batch->store_id);
                                $set('ends_at',    $batch->expiry_date?->format('Y-m-d'));
                                $set('nombre',     "Promo {$batch->product->name}");
                            }
                        })
                        ->columnSpanFull(),

                    // Campos ocultos llenados automáticamente
                    TextInput::make('product_id')->hidden(),
                    TextInput::make('store_id')->hidden(),
                ]),

            Section::make('Tipo de Descuento')
                ->icon('heroicon-o-tag')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la Promoción')
                        ->placeholder('Ej: Aspirina 3x2, 20% OFF caducidad')
                        ->required()
                        ->maxLength(100)
                        ->columnSpanFull(),

                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'porcentaje'  => '% Descuento',
                            'precio_fijo' => 'Precio Especial',
                            'nxm'         => 'NxM (lleva más, paga menos)',
                        ])
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText('Ej: "nxm" = 3x2, 2x1, etc.')
                        ->columnSpanFull(),

                    // Para porcentaje o precio fijo
                    TextInput::make('valor')
                        ->label(fn ($get) => match ($get('tipo')) {
                            'precio_fijo' => 'Precio especial ($)',
                            default       => 'Porcentaje de descuento',
                        })
                        ->numeric()
                        ->prefix(fn ($get) => $get('tipo') === 'precio_fijo' ? '$' : null)
                        ->suffix(fn ($get) => $get('tipo') === 'porcentaje'  ? '%' : null)
                        ->minValue(0.01)
                        ->visible(fn ($get) => in_array($get('tipo'), ['porcentaje', 'precio_fijo']))
                        ->required(fn ($get) => in_array($get('tipo'), ['porcentaje', 'precio_fijo'])),

                    // Para NxM
                    TextInput::make('cantidad_paga')
                        ->label('Cantidad que PAGA el cliente')
                        ->helperText('Ej: en "3x2" el cliente paga 2')
                        ->numeric()->integer()->minValue(1)
                        ->placeholder('2')
                        ->visible(fn ($get) => $get('tipo') === 'nxm')
                        ->required(fn ($get) => $get('tipo') === 'nxm'),

                    TextInput::make('cantidad_lleva')
                        ->label('Cantidad que LLEVA el cliente')
                        ->helperText('Ej: en "3x2" el cliente lleva 3')
                        ->numeric()->integer()->minValue(2)
                        ->placeholder('3')
                        ->visible(fn ($get) => $get('tipo') === 'nxm')
                        ->required(fn ($get) => $get('tipo') === 'nxm'),
                ]),

            Section::make('Activación')
                ->icon('heroicon-o-bolt')
                ->columns(2)
                ->schema([
                    Toggle::make('activa')
                        ->label('Activar manualmente ahora')
                        ->onColor('success')
                        ->helperText('También puedes dejar que se active sola por días.'),

                    TextInput::make('auto_activar_dias')
                        ->label('Auto-activar cuando queden ≤ X días')
                        ->numeric()->integer()->minValue(1)
                        ->suffix('días')
                        ->placeholder('30')
                        ->helperText('Se activa sola cuando el lote esté a estos días de caducar.'),

                    DatePicker::make('starts_at')
                        ->label('Válida desde')
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    DatePicker::make('ends_at')
                        ->label('Válida hasta')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->helperText('Se llena automáticamente con la fecha de caducidad del lote.'),
                ]),
        ]);
    }
}

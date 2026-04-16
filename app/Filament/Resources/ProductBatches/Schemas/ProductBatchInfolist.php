<?php

namespace App\Filament\Resources\ProductBatches\Schemas;

use App\Models\ProductBatch;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Producto')
                    ->icon('heroicon-o-cube')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Nombre')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-tag'),

                        TextEntry::make('product.category.name')
                            ->label('Categoría')
                            ->icon('heroicon-o-folder')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('product.stock')
                            ->label('Stock actual del producto')
                            ->icon('heroicon-o-archive-box')
                            ->badge()
                            ->color(fn ($record) => $record->product?->isLowStock() ? 'danger' : 'success')
                            ->formatStateUsing(fn ($state, $record) =>
                                $state . ' ' . ($record->product?->unidad ?? '')
                                . ($record->product?->isLowStock() ? ' — STOCK BAJO' : '')
                            ),
                    ]),

                Section::make('Lote')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('quantity')
                            ->label('Cantidad en este lote')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn ($state, $record) =>
                                $state . ' ' . ($record->product?->unidad ?? 'uds.')
                            ),

                        TextEntry::make('expiry_date')
                            ->label('Fecha de caducidad')
                            ->icon('heroicon-o-calendar-days')
                            ->date('d \d\e F \d\e Y')
                            ->placeholder('Sin fecha de caducidad')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                is_null($state)                                        => 'gray',
                                Carbon::parse($state)->startOfDay()->lte(Carbon::today()) => 'danger',
                                Carbon::parse($state)->diffInDays(Carbon::today(), true) <= 15 => 'warning',
                                default                                                => 'success',
                            }),

                        TextEntry::make('expiry_status')
                            ->label('Estado de caducidad')
                            ->icon('heroicon-o-clock')
                            ->state(fn (ProductBatch $record): string => match (true) {
                                is_null($record->expiry_date)                                         => 'Sin vencimiento',
                                $record->expiry_date->startOfDay()->lt(Carbon::today())               => 'Caducado hace ' . $record->expiry_date->diffInDays(Carbon::today(), true) . ' días',
                                $record->expiry_date->startOfDay()->isToday()                         => '¡Caduca HOY!',
                                $record->expiry_date->diffInDays(Carbon::today(), true) <= 7          => 'Caduca en ' . $record->expiry_date->diffInDays(Carbon::today(), true) . ' días',
                                $record->expiry_date->diffInDays(Carbon::today(), true) <= 15         => 'Caduca pronto (' . $record->expiry_date->diffInDays(Carbon::today(), true) . ' días)',
                                default                                                               => 'Vigente — ' . $record->expiry_date->diffInDays(Carbon::today(), true) . ' días restantes',
                            })
                            ->badge()
                            ->color(fn (ProductBatch $record): string => match (true) {
                                is_null($record->expiry_date)                                         => 'gray',
                                $record->expiry_date->startOfDay()->lte(Carbon::today())              => 'danger',
                                $record->expiry_date->diffInDays(Carbon::today(), true) <= 15         => 'warning',
                                default                                                               => 'success',
                            }),
                    ]),

                Section::make('Promoción')
                    ->icon('heroicon-o-sparkles')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('promo_nombre')
                            ->label('Nombre')
                            ->state(fn (ProductBatch $record): string =>
                                $record->load('promotions')->promocionActiva()?->nombre ?? '—'
                            )
                            ->icon('heroicon-o-tag')
                            ->badge()
                            ->color(fn ($state) => $state === '—' ? 'gray' : 'success'),

                        TextEntry::make('promo_tipo')
                            ->label('Tipo')
                            ->state(fn (ProductBatch $record): string =>
                                match ($record->promocionActiva()?->tipo) {
                                    'porcentaje'  => 'Descuento %',
                                    'precio_fijo' => 'Precio especial',
                                    'nxm'         => 'NxM',
                                    default       => '—',
                                }
                            )
                            ->badge()
                            ->color(fn ($state) => $state === '—' ? 'gray' : 'warning'),

                        TextEntry::make('promo_valor')
                            ->label('Valor / Condición')
                            ->state(fn (ProductBatch $record): string => match ($record->promocionActiva()?->tipo) {
                                'porcentaje'  => $record->promocionActiva()->valor . '%',
                                'precio_fijo' => '$' . number_format($record->promocionActiva()->valor, 2),
                                'nxm'         => $record->promocionActiva()->cantidad_lleva . 'x' . $record->promocionActiva()->cantidad_paga,
                                default       => '—',
                            })
                            ->badge()
                            ->color(fn ($state) => $state === '—' ? 'gray' : 'warning'),
                    ]),

                Section::make('Registro')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-pencil-square'),
                    ]),
            ]);
    }
}

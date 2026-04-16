<?php

namespace App\Filament\Resources\Adjustments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mermas y Devoluciones')
                    ->description('Información detallada sobre mermas y devoluciones relacionadas con el ajuste.')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary')
                    ->columns(2)
                    ->columnSpan(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('store.name')
                            ->label('Tienda')
                            ->icon('heroicon-o-building-storefront')
                            ->iconColor('success'),
                        TextEntry::make('product.name')
                            ->label('Producto')
                            ->icon('heroicon-o-cube')
                            ->iconColor('warning'),
                        TextEntry::make('user.name')
                            ->label('Usuario')
                            ->icon('heroicon-o-user')
                            ->iconColor('info'),
                        TextEntry::make('type')
                            ->label('Tipo de Ajuste')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->iconColor('danger'),
                        TextEntry::make('quantity')
                            ->label('Cantidad')
                            ->icon('heroicon-o-calculator')
                            ->iconColor('primary'),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->icon('heroicon-o-document-text')
                            ->iconColor('secondary'),
                    ]),
            ]);
    }
}

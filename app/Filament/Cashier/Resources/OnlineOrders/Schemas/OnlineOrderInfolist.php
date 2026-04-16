<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Schemas;

use Dom\Text;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OnlineOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Pedido')
                    ->label('Información del Pedido')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Folio')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-hashtag')
                            ->iconColor('primary')
                            ->prefix('#00'),
                        TextEntry::make('customer_name')
                            ->label('Cliente')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-user')
                            ->iconColor('primary'),
                        TextEntry::make('customer_phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->weight('medium')
                            ->iconColor('primary'),
                        TextEntry::make('total')
                            ->label('Total a Cobrar')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-currency-dollar')
                            ->iconColor('success')
                            ->color('success')
                            ->money('MXN'),
                        TextEntry::make('notes')
                            ->label('Notas del Cliente')
                            ->icon('heroicon-o-chat-bubble-oval-left')
                            ->weight('medium')
                            ->iconColor('primary')
                            ->formatStateUsing(fn (?string $state) => $state ?? 'Ninguna'),
                    ])->columnSpanFull(),
                    Section::make('Lista de Productos (Armar Bolsa)')
                    ->columns(1)
                    ->schema([
                            ViewEntry::make('items')
                                ->label('Productos')
                                ->view('filament.infolists.components.order-items-table'),
                    ])->columnSpanFull(),
                    
            ]);
    }
}

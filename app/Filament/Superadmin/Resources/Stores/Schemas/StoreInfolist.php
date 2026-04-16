<?php

namespace App\Filament\Superadmin\Resources\Stores\Schemas;

use App\Models\Store;
use Carbon\Carbon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // Columna izquierda (2/3)
                        Section::make('Tienda')
                            ->icon('heroicon-o-building-storefront')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nombre')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->icon('heroicon-o-building-storefront')
                                    ->columnSpanFull(),

                                TextEntry::make('business_type')
                                    ->label('Tipo de negocio')
                                    ->icon('heroicon-o-tag')
                                    ->badge()
                                    ->color('info')
                                    ->formatStateUsing(fn (?string $state) =>
                                        Store::businessTypes()[$state] ?? $state ?? '—'
                                    ),

                                TextEntry::make('estatus')
                                    ->label('Estado')
                                    ->icon('heroicon-o-signal')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'activo'     => 'success',
                                        'suspendido' => 'warning',
                                        'cancelado'  => 'danger',
                                        default      => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'activo'     => 'Activo',
                                        'suspendido' => 'Suspendido',
                                        'cancelado'  => 'Cancelado',
                                        default      => $state,
                                    }),

                                TextEntry::make('rfc')
                                    ->label('RFC')
                                    ->icon('heroicon-o-document-text')
                                    ->placeholder('—')
                                    ->copyable(),

                                TextEntry::make('phone')
                                    ->label('Teléfono')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('—')
                                    ->copyable(),

                                TextEntry::make('whatsapp_number')
                                    ->label('WhatsApp')
                                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                    ->placeholder('—')
                                    ->copyable(),

                                TextEntry::make('address')
                                    ->label('Dirección')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                TextEntry::make('catalog_description')
                                    ->label('Descripción del catálogo')
                                    ->icon('heroicon-o-chat-bubble-oval-left')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),

                        // Columna derecha (1/3)
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Logo')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        ImageEntry::make('logo')
                                            ->label('')
                                            ->disk('public')
                                            ->height(120)
                                            ->placeholder('Sin logo'),
                                    ]),

                                Section::make('Suscripción')
                                    ->icon('heroicon-o-credit-card')
                                    ->schema([
                                        TextEntry::make('plan.name')
                                            ->label('Plan')
                                            ->icon('heroicon-o-sparkles')
                                            ->badge()
                                            ->color('primary')
                                            ->placeholder('Sin plan'),

                                        TextEntry::make('valid_until')
                                            ->label('Válida hasta')
                                            ->icon('heroicon-o-calendar-days')
                                            ->date('d/m/Y')
                                            ->placeholder('—')
                                            ->badge()
                                            ->color(fn ($state): string => match (true) {
                                                is_null($state)                                        => 'gray',
                                                Carbon::parse($state)->isPast()                        => 'danger',
                                                Carbon::parse($state)->diffInDays(now(), true) <= 7    => 'warning',
                                                default                                                => 'success',
                                            }),

                                        TextEntry::make('valid_until_status')
                                            ->label('Estado')
                                            ->icon('heroicon-o-clock')
                                            ->state(fn (Store $record): string => match (true) {
                                                is_null($record->valid_until)                                      => '—',
                                                Carbon::parse($record->valid_until)->isPast()                      => 'Vencida hace ' . Carbon::parse($record->valid_until)->diffInDays(now(), true) . ' días',
                                                Carbon::parse($record->valid_until)->diffInDays(now(), true) <= 7  => 'Vence en ' . Carbon::parse($record->valid_until)->diffInDays(now(), true) . ' días',
                                                default                                                            => 'Vigente — ' . Carbon::parse($record->valid_until)->diffInDays(now(), true) . ' días restantes',
                                            })
                                            ->badge()
                                            ->color(fn (Store $record): string => match (true) {
                                                is_null($record->valid_until)                                      => 'gray',
                                                Carbon::parse($record->valid_until)->isPast()                      => 'danger',
                                                Carbon::parse($record->valid_until)->diffInDays(now(), true) <= 7  => 'warning',
                                                default                                                            => 'success',
                                            }),

                                        TextEntry::make('trial_ends_at')
                                            ->label('Trial hasta')
                                            ->icon('heroicon-o-beaker')
                                            ->date('d/m/Y')
                                            ->placeholder('Sin trial')
                                            ->badge()
                                            ->color(fn ($state): string => match (true) {
                                                is_null($state)                 => 'gray',
                                                Carbon::parse($state)->isPast() => 'danger',
                                                default                         => 'warning',
                                            }),
                                    ]),
                            ]),
                    ]),

                // Sección dueño — ancho completo
                Section::make('Dueño')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('owner.name')
                            ->label('Nombre')
                            ->icon('heroicon-o-user')
                            ->weight('bold'),

                        TextEntry::make('owner.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('owner.phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('—')
                            ->copyable(),
                    ]),

                Section::make('Registro')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Actualizada')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-pencil-square'),
                    ]),
            ]);
    }
}

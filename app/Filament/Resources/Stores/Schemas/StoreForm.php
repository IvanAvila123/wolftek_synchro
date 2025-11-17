<?php

namespace App\Filament\Resources\Stores\Schemas;

use App\Models\Store;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Pest\Support\Str;
use Ramsey\Collection\Set;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) =>
                        // Genera el 'slug' automáticamente a partir del nombre
                        $set('slug', Str::slug($state))
                    ),
                TextInput::make('logo_url')
                    ->image()
                    ->directory('store-logos')
                    ->label('Logo de la tienda'),
                TextInput::make('whatsapp_number')
                    ->required()
                    ->tel()
                    ->helperText('Número para recibir pedidos (ej. 525512345678)'),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Store::class, 'slug', ignoreRecord: true)
                    ->helperText('URL única para la tienda (ej. /pide/mi-tienda)'),

                Section::make('Configuración de Pagos')
                ->description('Credenciales para que la tienda reciba pagos.')
                ->collapsible()
                ->schema([
                TextInput::make('mercado_pago_key')
                    ->label('Llave de Mercado Pago')
                    ->password() // Oculta la llave
                    ->maxLength(255),
                TextInput::make('clabe_interbancaria')
                    ->label('CLABE Interbancaria')
                    ->length(18)
                    ->numeric(),
                ])->columns(1),
            ]);
    }
}

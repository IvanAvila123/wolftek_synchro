<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OnlineOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('store_id')
                    ->required()
                    ->numeric(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel()
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('cart_items')
                    ->required(),
            ]);
    }
}

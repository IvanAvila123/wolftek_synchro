<?php

namespace App\Filament\Cashier\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('store_id')
                    ->numeric(),
                TextEntry::make('cash_shift_id')
                    ->numeric(),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('customer_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('payment_method')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Pages;

use App\Filament\Cashier\Resources\OnlineOrders\OnlineOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOnlineOrders extends ListRecords
{
    protected static string $resource = OnlineOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}

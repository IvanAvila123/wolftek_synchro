<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Pages;

use App\Filament\Cashier\Resources\OnlineOrders\OnlineOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOnlineOrder extends CreateRecord
{
    protected static string $resource = OnlineOrderResource::class;
}

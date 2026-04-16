<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Pages;

use App\Filament\Cashier\Resources\OnlineOrders\OnlineOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOnlineOrder extends EditRecord
{
    protected static string $resource = OnlineOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

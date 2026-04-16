<?php

namespace App\Filament\Cashier\Resources\OnlineOrders\Pages;

use App\Filament\Cashier\Resources\OnlineOrders\OnlineOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewOnlineOrder extends ViewRecord
{
    protected static string $resource = OnlineOrderResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Pedido en línea #{$this->record->id} — {$this->record->customer_name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}

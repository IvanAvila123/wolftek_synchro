<?php

namespace App\Filament\Cashier\Resources\Sales\Pages;

use App\Filament\Cashier\Resources\Sales\SaleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}

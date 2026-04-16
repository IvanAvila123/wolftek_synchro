<?php

namespace App\Filament\Cashier\Resources\Sales\Pages;

use App\Filament\Cashier\Resources\Sales\SaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Ventas';
    }

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}

<?php

namespace App\Filament\Cashier\Resources\Expenses\Pages;

use App\Filament\Cashier\Resources\Expenses\ExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Gastos';
    }

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}

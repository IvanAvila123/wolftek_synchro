<?php

namespace App\Filament\Cashier\Resources\Expenses\Pages;

use App\Filament\Cashier\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}

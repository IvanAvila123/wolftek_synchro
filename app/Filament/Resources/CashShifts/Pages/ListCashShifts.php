<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashShifts extends ListRecords
{
    protected static string $resource = CashShiftResource::class;

    protected static ?string $title = 'Control de Cajas';

}

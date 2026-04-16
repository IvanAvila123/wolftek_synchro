<?php

namespace App\Filament\Resources\CashShifts\Pages;

use App\Filament\Resources\CashShifts\CashShiftResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashShift extends EditRecord
{
    protected static string $resource = CashShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

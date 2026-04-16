<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    public function getTitle(): string
    {
        return 'Editar Compra ' . $this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
            ->label('Ver Detalles'),
        ];
    }
}

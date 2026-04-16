<?php

namespace App\Filament\Resources\ProductBatches\Pages;

use App\Filament\Resources\ProductBatches\ProductBatchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProductBatch extends ViewRecord
{
    protected static string $resource = ProductBatchResource::class;

    public function getTitle(): string
    {
        return "Lote #{$this->record->id} — {$this->record->product->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
            ->label('Editar lote'),
        ];
    }
}

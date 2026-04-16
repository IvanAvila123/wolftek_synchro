<?php

namespace App\Filament\Resources\Adjustments\Pages;

use App\Filament\Resources\Adjustments\AdjustmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewAdjustment extends ViewRecord
{
    protected static string $resource = AdjustmentResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Detalle del Ajuste #' . $this->record->id .  ' - ' . $this->record->product->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
            ->label('Editar Merma'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ProductBatches\Pages;

use App\Filament\Pages\UpgradePlan;
use App\Filament\Resources\ProductBatches\ProductBatchResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListProductBatches extends ListRecords
{
    protected static string $resource = ProductBatchResource::class;

    public function mount(): void
    {
        if (! Filament::getTenant()?->hasFeature('batches')) {
            $this->redirect(UpgradePlan::getUrl(['feature' => 'batches']));
            return;
        }
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Registrar Nuevo Lote')
            ->icon('heroicon-o-plus-circle')
            ->slideOver()
            ->createAnother(false),
        ];
    }
}

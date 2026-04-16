<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Pages\UpgradePlan;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    public function mount(): void
    {
        if (! Filament::getTenant()?->hasFeature('suppliers')) {
            $this->redirect(UpgradePlan::getUrl(['feature' => 'suppliers']));
            return;
        }
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Nueva Compra'),
        ];
    }
}

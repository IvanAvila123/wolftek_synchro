<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Pages\UpgradePlan;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function mount(): void
    {
        if (! Filament::getTenant()?->hasFeature('customers')) {
            $this->redirect(UpgradePlan::getUrl(['feature' => 'customers']));
            return;
        }
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

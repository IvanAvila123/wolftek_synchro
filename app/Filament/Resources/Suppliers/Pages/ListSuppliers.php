<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Pages\UpgradePlan;
use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    public function mount(): void
    {
        if (! Filament::getTenant()?->hasFeature('suppliers')) {
            $this->redirect(UpgradePlan::getUrl(['feature' => 'suppliers']));
            return;
        }
        parent::mount();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Proveedores';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Crear Nuevo Provedor'),
        ];
    }
}

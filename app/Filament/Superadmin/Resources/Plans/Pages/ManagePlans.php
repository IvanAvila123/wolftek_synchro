<?php

namespace App\Filament\Superadmin\Resources\Plans\Pages;

use App\Filament\Superadmin\Resources\Plans\PlanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlans extends ManageRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Crear nuevo plan')
            ->createAnotherAction(fn (Action $action) => $action->label('Crear y crear otro'))
        ];
    }
}

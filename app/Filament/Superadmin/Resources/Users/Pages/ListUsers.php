<?php

namespace App\Filament\Superadmin\Resources\Users\Pages;

use App\Filament\Superadmin\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear usuario')
                ->createAnotherAction(fn (Action $action) => $action->label('Crear y crear otro')),
        ];
    }
}

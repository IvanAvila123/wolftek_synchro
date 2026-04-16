<?php

namespace App\Filament\Superadmin\Resources\Subscriptions\Pages;

use App\Filament\Superadmin\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nueva Suscripción'),
        ];
    }
}

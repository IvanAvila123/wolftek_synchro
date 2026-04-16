<?php

namespace App\Filament\Superadmin\Resources\Subscriptions\Pages;

use App\Filament\Superadmin\Resources\Subscriptions\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

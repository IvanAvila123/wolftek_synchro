<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Store;
use Filament\Pages\Page;

class RegisterStore extends Page
{
    protected string $view = 'filament.pages.tenancy.register-store';

    protected function handleRegistration(array $data): Store
    {
        $store = Store::create($data);

        $store->members()->attach(auth()->user());

        return $store;

    }
}

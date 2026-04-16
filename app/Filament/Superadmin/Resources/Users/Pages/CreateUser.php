<?php

namespace App\Filament\Superadmin\Resources\Users\Pages;

use App\Filament\Superadmin\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Crear usuario';
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Solo creamos el usuario base.
        // La tienda, el empleado y el rol de "Owner" se crearán
        // solos cuando el cliente inicie sesión por primera vez y llene su formulario.
        return User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
            'phone'     => $data['phone'] ?? null,
            'is_active' => true,
        ]);
    }
}
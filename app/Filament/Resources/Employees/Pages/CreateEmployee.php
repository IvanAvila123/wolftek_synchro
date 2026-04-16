<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
{
    // 1. Crear el usuario
    $user = User::create([
        'name'      => $data['name'],
        'email'     => $data['email'],
        'password'  => bcrypt($data['password']),
        'phone'     => $data['phone'] ?? null,
        'is_active' => true,
    ]);

    // 2. Asignar el rol dentro del contexto del tenant
    $storeId = filament()->getTenant()->id;
    setPermissionsTeamId($storeId);
    $user->assignRole($data['role']);

    // 3. Crear el empleado
    $employee = Employee::create([
        'user_id'         => $user->id,
        'store_id'        => $storeId,
        'rfc'             => $data['rfc'] ?? null,
        'curp'            => $data['curp'] ?? null,
        'fiscal_document' => $data['fiscal_document'] ?? null,
    ]);

    return $employee;
}

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
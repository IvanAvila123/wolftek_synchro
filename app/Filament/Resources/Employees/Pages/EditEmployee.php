<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <-- No olvides importar DB

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $user = $record->user;

            // 1. Actualizar datos del usuario
            $userData = [
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];

            if (filled($data['password'] ?? null)) {
                $userData['password'] = bcrypt($data['password']);
            }

            $user->update($userData);

            // 2. Actualizar rol
            if (filled($data['role'] ?? null)) {
                $storeId = filament()->getTenant()->id;
                setPermissionsTeamId($storeId);
                $user->syncRoles([$data['role']]);
            }
            
            // 3. Actualizar datos del empleado
            $record->update([
                'rfc'             => $data['rfc'] ?? null,
                'curp'            => $data['curp'] ?? null,
                'fiscal_document' => $data['fiscal_document'] ?? null,
            ]);

            return $record;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
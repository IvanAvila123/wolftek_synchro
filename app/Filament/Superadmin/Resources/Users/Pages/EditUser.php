<?php

namespace App\Filament\Superadmin\Resources\Users\Pages;

use App\Filament\Superadmin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <-- Importar DB

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
            $role = $data['role'] ?? null;
            unset($data['role']);

            $record->update($data);

            if (filled($role)) {
                if ($record->store) {
                    setPermissionsTeamId($record->store->id);
                } elseif ($record->employee) {
                    setPermissionsTeamId($record->employee->store_id);
                } else {
                    setPermissionsTeamId(null);
                }
                $record->syncRoles([$role]);
            }

            return $record;
        });
    }
}
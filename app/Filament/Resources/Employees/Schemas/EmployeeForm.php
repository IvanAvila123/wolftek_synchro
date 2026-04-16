<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del empleado')
                    ->description('Datos personales y acceso al sistema')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->placeholder('Ej: Juan Pérez López')
                            ->maxLength(255)
                            ->autofocus()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('name', ucwords($state)))
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->placeholder('empleado@correo.com')
                            ->email()
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignorable: fn($record) => $record?->user
                            )
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->user?->email);
                                }
                            })
                            ->required(),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->placeholder('55 1234 5678')
                            ->tel()
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->user?->phone);
                                }
                            })
                            ->nullable(),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->placeholder('Mínimo 8 caracteres')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText(
                                fn(string $operation): ?string =>
                                $operation === 'edit' ? 'Déjalo vacío para mantener la actual.' : null
                            ),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->placeholder('Repite la contraseña')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->requiredWith('password')
                            ->saved(false),
                    ]),

                Section::make('Rol y Datos Fiscales')
                    ->description('Asignación de rol y documentación fiscal')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->label('Rol del empleado')
                            ->options(function () {
                                $storeId = filament()->getTenant()?->id;
                                return \App\Models\Role::where('store_id', $storeId)
                                    ->whereNotIn('name', ['owner'])
                                    ->pluck('name', 'name')
                                    ->map(fn($name) => ucfirst($name));
                            })
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->user?->getDirectRoleName());
                                }
                            })
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('rfc')
                            ->label('RFC')
                            ->placeholder('XAXX010101000')
                            ->afterStateUpdatedJs(<<<'JS'
                                $set('rfc', ($state ?? '').toUpperCase())
                            JS)
                            ->maxLength(13)
                            ->nullable(),

                        TextInput::make('curp')
                            ->label('CURP')
                            ->placeholder('XEXX010101HCXXXX00')
                            ->afterStateUpdatedJs(<<<'JS'
                                $set('curp', ($state ?? '').toUpperCase())
                            JS)
                            ->maxLength(18)
                            ->nullable(),

                        FileUpload::make('fiscal_document')
                            ->label('Constancia de situación fiscal')
                            ->disk('public')
                            ->directory('employee-documents')
                            ->maxSize(1024)
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

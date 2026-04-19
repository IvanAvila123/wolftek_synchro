<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use App\Models\Permission;
use App\Models\Role;

class RegisterStore extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Registrar mi Tienda';
    }

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_type')
                    ->label('Tipo de negocio')
                    ->options(Store::businessTypes())
                    ->default('otro')
                    ->required()
                    ->live()
                    ->helperText('Selecciona el tipo de negocio para configurar categorías y opciones automáticamente.'),
                TextInput::make('name')
                    ->label('Nombre del negocio')
                    ->required()
                    ->maxLength(255)
                    ->autofocus()
                    ->placeholder(fn ($get) => match ($get('business_type')) {
                        'abarrotes'     => 'Ej: Abarrotes Don Pepe',
                        'dulceria'      => 'Ej: Dulcería La Golosa',
                        'refaccionaria' => 'Ej: Refaccionaria El Pistón',
                        'ferreteria'    => 'Ej: Ferretería El Clavo',
                        'farmacia'      => 'Ej: Farmacia San José',
                        'papeleria'     => 'Ej: Papelería El Lápiz',
                        'carniceria'    => 'Ej: Carnicería El Corte',
                        'panaderia'     => 'Ej: Panadería El Pan Nuestro',
                        'ropa'          => 'Ej: Boutique La Moda',
                        'electronica'   => 'Ej: TecnoStore',
                        default         => 'Ej: Mi Negocio',
                    }),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->placeholder('55 1234 5678')
                    ->nullable(),
                TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Calle, Colonia, CP, Ciudad')
                    ->nullable(),
                TextInput::make('rfc')
                    ->label('RFC')
                    ->placeholder('XAXX010101000')
                    ->afterStateUpdatedJs(<<<'JS'
                        $set('rfc', ($state ?? '').toUpperCase())
                    JS)
                    ->maxLength(13)
                    ->nullable(),
                Select::make('plan_id')
                    ->label('Plan')
                    ->options(Plan::pluck('name', 'id'))
                    ->default(1)
                    ->required(),
            ]);
    }

    protected function handleRegistration(array $data): Store
    {
        // 1. Crear la tienda
        $store = Store::create([
            ...$data,
            'user_id' => auth()->id(),
            'status' => 'trial', // Le asignamos el estatus de prueba
            'trial_ends_at' => now()->addDays(7), // Calculamos 7 días a partir de hoy
            'is_active' => true, // La dejamos encendida para que puedan entrar
        ]);

        // 2. Crear roles default dentro del tenant
        setPermissionsTeamId($store->id);

        $ownerRole = Role::firstOrCreate(
            ['name' => 'owner', 'guard_name' => 'web', 'store_id' => $store->id],
            ['panel' => 'admin']
        );

        Role::firstOrCreate(
            ['name' => 'manager', 'guard_name' => 'web', 'store_id' => $store->id],
            ['panel' => 'admin']
        );

        Role::firstOrCreate(
            ['name' => 'cashier', 'guard_name' => 'web', 'store_id' => $store->id],
            ['panel' => 'cashier']
        );

        // 3. Asignar TODOS los permisos al owner
        $ownerRole->syncPermissions(Permission::all());

        // 4. Asignar rol al usuario
        auth()->user()->assignRole('owner');

        // 5. Limpiar caché de permisos
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 6. Crear categorías predeterminadas según el tipo de negocio
        $defaultCategories = Store::defaultCategoriesFor($store->business_type ?? 'otro');
        foreach ($defaultCategories as $categoryName) {
            Category::create([
                'name'     => $categoryName,
                'store_id' => $store->id,
            ]);
        }

        return $store;
    }
}

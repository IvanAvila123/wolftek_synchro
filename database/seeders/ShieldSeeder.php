<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[{"id":1,"user_id":3,"plan_id":1,"name":"Abarroteria la flor","rfc":"SIN RFC","address":"Calle saturno 90, la capulina, ixtapaluca, edomex, edomex, 56532","phone":"5598042389","email":null,"logo":null,"estatus":"activo","trial_ends_at":null,"created_at":"2026-03-08T04:05:00.000000Z","updated_at":"2026-03-08T04:05:00.000000Z"},{"id":2,"user_id":6,"plan_id":1,"name":"Dulceria Candy","rfc":"SIN RFC","address":"Calle saturno 90, la capulina, ixtapaluca, edomex, edomex, 56532","phone":"5678093241","email":null,"logo":null,"estatus":"activo","trial_ends_at":null,"created_at":"2026-03-08T07:23:21.000000Z","updated_at":"2026-03-08T07:23:21.000000Z"},{"id":3,"user_id":7,"plan_id":1,"name":"Abarroteria gabriel","rfc":"SIN RFC","address":"Calle saturno 90, la capulina, ixtapaluca, edomex, edomex, 56532","phone":"5543217897","email":null,"logo":null,"estatus":"activo","trial_ends_at":null,"created_at":"2026-03-08T07:28:21.000000Z","updated_at":"2026-03-08T07:28:21.000000Z"}]';
        $users = '[{"id":1,"name":"Jesus Ivan Avila Ramirez","email":"ivanavilar456@gmail.com","email_verified_at":null,"phone":null,"is_active":1,"created_at":"2026-03-08T03:41:24.000000Z","updated_at":"2026-04-01T02:13:35.000000Z","ui_preferences":{"ui.color":"#6366f1"},"tenant_roles":{"_global":["super_admin"]},"tenant_permissions":[]},{"id":3,"name":"Due\\u00f1o Prueba","email":"dueno@prueba.com","email_verified_at":null,"phone":"5598042389","is_active":1,"created_at":"2026-03-08T04:01:10.000000Z","updated_at":"2026-04-01T02:05:19.000000Z","ui_preferences":{"ui.color":"#22c55e","ui.layout":"sidebar-collapsed"},"tenant_roles":{"1":["owner"]},"tenant_permissions":[]},{"id":4,"name":"Ricardo Hernandez Diaz","email":"ricardo@abarroteriaflor.com","email_verified_at":null,"phone":"5598042389","is_active":1,"created_at":"2026-03-08T04:39:46.000000Z","updated_at":"2026-03-08T04:48:38.000000Z","ui_preferences":{"ui.color":"#22c55e"},"tenant_roles":{"1":["manager"]},"tenant_permissions":[]},{"id":5,"name":"Cristina Hernandez Castillo","email":"cristina@abarroteriaflor.com","email_verified_at":null,"phone":"5634029834","is_active":1,"created_at":"2026-03-08T04:57:14.000000Z","updated_at":"2026-03-08T23:52:25.000000Z","ui_preferences":{"ui.color":"#ef4444","ui.layout":"topbar"},"tenant_roles":{"1":["cashier"]},"tenant_permissions":[]},{"id":6,"name":"Due\\u00f1o Prueba 2","email":"dueno2@prueba.com","email_verified_at":null,"phone":"5509347612","is_active":1,"created_at":"2026-03-08T07:21:15.000000Z","updated_at":"2026-03-08T07:50:39.000000Z","ui_preferences":{"ui.color":"#ec4899","ui.layout":"sidebar-collapsed"},"tenant_roles":{"2":["owner"]},"tenant_permissions":[]},{"id":7,"name":"Due\\u00f1o Prueba 3","email":"dueno3@prueba.com","email_verified_at":null,"phone":"5543217897","is_active":1,"created_at":"2026-03-08T07:27:42.000000Z","updated_at":"2026-03-08T07:27:42.000000Z","ui_preferences":null,"tenant_roles":{"3":["owner"]},"tenant_permissions":[]}]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":[],"store_id":null},{"name":"owner","guard_name":"web","permissions":["ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:Plan","View:Plan","Create:Plan","Update:Plan","Delete:Plan","Restore:Plan","ForceDelete:Plan","ForceDeleteAny:Plan","RestoreAny:Plan","Replicate:Plan","Reorder:Plan","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee"],"store_id":1},{"name":"manager","guard_name":"web","permissions":["ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Restore:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee","ViewAny:CashRegister","View:CashRegister","Create:CashRegister","Update:CashRegister","Delete:CashRegister","Restore:CashRegister","ForceDelete:CashRegister","ForceDeleteAny:CashRegister","RestoreAny:CashRegister","Replicate:CashRegister","Reorder:CashRegister","ViewAny:CashShift","View:CashShift","Create:CashShift","Update:CashShift","Delete:CashShift","Restore:CashShift","ForceDelete:CashShift","ForceDeleteAny:CashShift","RestoreAny:CashShift","Replicate:CashShift","Reorder:CashShift","ViewAny:Customer","View:Customer","Create:Customer","Update:Customer","Delete:Customer","Restore:Customer","ForceDelete:Customer","ForceDeleteAny:Customer","RestoreAny:Customer","Replicate:Customer","Reorder:Customer","ViewAny:Product","View:Product","Create:Product","Update:Product","Delete:Product","Restore:Product","ForceDelete:Product","ForceDeleteAny:Product","RestoreAny:Product","Replicate:Product","Reorder:Product"],"store_id":1},{"name":"cashier","guard_name":"web","permissions":[],"store_id":1},{"name":"owner","guard_name":"web","permissions":["ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:Plan","View:Plan","Create:Plan","Update:Plan","Delete:Plan","Restore:Plan","ForceDelete:Plan","ForceDeleteAny:Plan","RestoreAny:Plan","Replicate:Plan","Reorder:Plan","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee"],"store_id":2},{"name":"manager","guard_name":"web","permissions":[],"store_id":2},{"name":"cashier","guard_name":"web","permissions":[],"store_id":2},{"name":"owner","guard_name":"web","permissions":["ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:Plan","View:Plan","Create:Plan","Update:Plan","Delete:Plan","Restore:Plan","ForceDelete:Plan","ForceDeleteAny:Plan","RestoreAny:Plan","Replicate:Plan","Reorder:Plan","ViewAny:User","View:User","Create:User","Update:User","Delete:User","Restore:User","ForceDelete:User","ForceDeleteAny:User","RestoreAny:User","Replicate:User","Reorder:User","ViewAny:Category","View:Category","Create:Category","Update:Category","Delete:Category","Restore:Category","ForceDelete:Category","ForceDeleteAny:Category","RestoreAny:Category","Replicate:Category","Reorder:Category","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee"],"store_id":3},{"name":"manager","guard_name":"web","permissions":[],"store_id":3},{"name":"cashier","guard_name":"web","permissions":[],"store_id":3}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = 'App\Models\Store';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = true;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = 'store_user';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'store_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = true;
        $teamForeignKey = 'store_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}

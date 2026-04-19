<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Permission;
use App\Models\Role;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('es');

        // 👇 2. LA LLAVE MAESTRA 👇
        Gate::before(function ($user, $ability) {
            // Usamos la función personalizada que creaste en User.php
            if ($user->hasDirectRole(['super_admin'])) {
                return true; // Acceso total concedido
            }
            
            return null; // Si no es super admin, que siga el flujo normal y revise la base de datos
        });
    
        app(PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        //
    }
}

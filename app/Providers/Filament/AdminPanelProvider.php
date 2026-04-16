<?php

namespace App\Providers\Filament;

use Andreia\FilamentUiSwitcher\FilamentUiSwitcherPlugin;
use App\Http\Middleware\CheckStoreStatus;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName(fn() => filament()->getTenant()?->name ?? 'WolftekPOS')
            ->brandLogo(fn() => filament()->getTenant()?->logo_url ?? null)
            ->brandLogoHeight('40px')
            ->tenant(Store::class, ownershipRelationship: 'store')
            ->tenantRegistration(\App\Filament\Pages\RegisterStore::class)
            ->tenantMenuItems([
                'register' => fn(Action $action) => $action
                    ->visible(function () {
                        $user = auth()->user();
                        $store = $user?->store;

                        if (!$store) return true;

                        if ($store->plan?->max_branches === 1) return false;

                        return true;
                    })
            ])
            ->login()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([

            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckStoreStatus::class,
            ])
            ->tenantMiddleware([
                \App\Http\Middleware\SetPermissionsTeamId::class,
                \BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant::class,
            ], isPersistent: true)

            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->scopeToTenant(true)
                    ->navigationLabel('Roles y Permisos') // Cambia el nombre del botón
                    ->navigationGroup('Configuración') // Lo mueve a la carpeta de Configuración
                    ->tenantOwnershipRelationshipName('store'),
                FilamentUiSwitcherPlugin::make()
                    ->withModeSwitcher(),
            ]);
    }
}

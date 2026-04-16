<?php

namespace App\Providers\Filament;

use Andreia\FilamentUiSwitcher\FilamentUiSwitcherPlugin;
use App\Http\Middleware\CheckStoreStatus;
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

class CashierPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('cashier')
            ->path('cashier')
            ->login()
            ->brandName(fn() => filament()->getTenant()?->name ?? 'WolftekPOS - Caja')
            ->tenant(\App\Models\Store::class, ownershipRelationship: 'store')
            ->tenantMiddleware([
                \App\Http\Middleware\SetPermissionsTeamId::class,
                \App\Http\Middleware\EnsureOpenCashShift::class,
            ], isPersistent: true)
            ->tenantMenuItems([
                'register' => \Filament\Navigation\MenuItem::make()->visible(false),
            ])
            ->viteTheme('resources/css/filament/cashier/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            ->discoverResources(in: app_path('Filament/Cashier/Resources'), for: 'App\Filament\Cashier\Resources')
            ->discoverPages(in: app_path('Filament/Cashier/Pages'), for: 'App\Filament\Cashier\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Cashier/Widgets'), for: 'App\Filament\Cashier\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ->plugins([
                FilamentUiSwitcherPlugin::make()
                    ->withModeSwitcher(), // agrega dark/light mode
            ])

            ->renderHook(
                'panels::body.start',
                fn(): string => view('filament.hooks.stock-warning'),
            );
    }
}

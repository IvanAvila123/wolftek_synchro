<?php

namespace App\Filament\Superadmin\Widgets;

use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SuperadminStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Resumen de la Plataforma';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $now = Carbon::now();

        // Tiendas activas con suscripción pagada
        $tiendasActivas = Store::where('is_active', true)
            ->where('estatus', 'active')
            ->count();

        // MRR estimado: suma de precios de plan de tiendas activas pagando
        $mrr = Store::where('is_active', true)
            ->where('estatus', 'active')
            ->join('plans', 'stores.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        // Trials activos
        $trialsActivos = Store::where('estatus', 'trial')
            ->where('trial_ends_at', '>=', $now)
            ->count();

        // Trials que vencen en los próximos 7 días
        $trialsPorVencer = Store::where('estatus', 'trial')
            ->whereBetween('trial_ends_at', [$now, $now->copy()->addDays(7)])
            ->count();

        // Tiendas con problema de pago (requieren atención)
        $tiendasProblema = Store::whereIn('estatus', ['past_due', 'unpaid'])
            ->count();

        // Tiendas nuevas este mes
        $nuevasEsteMes = Store::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Total de usuarios en la plataforma
        $totalUsuarios = User::count();

        // Tiendas por vencer en 30 días (valid_until)
        $tiendas30Dias = Store::where('estatus', 'active')
            ->whereBetween('valid_until', [$now, $now->copy()->addDays(30)])
            ->count();

        return [
            Stat::make('MRR Estimado', '$' . number_format($mrr, 2))
                ->description('Ingresos recurrentes mensuales')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([4, 6, 5, 8, 7, 9, 12]),

            Stat::make('Tiendas Activas', $tiendasActivas)
                ->description("{$nuevasEsteMes} nuevas este mes")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary')
                ->chart([2, 3, 4, 3, 5, 4, $tiendasActivas]),

            Stat::make('Trials Activos', $trialsActivos)
                ->description($trialsPorVencer > 0 ? "⚠️ {$trialsPorVencer} vencen en 7 días" : 'Sin vencer pronto')
                ->descriptionIcon('heroicon-m-clock')
                ->color($trialsPorVencer > 0 ? 'warning' : 'info'),

            Stat::make('Requieren Atención', $tiendasProblema)
                ->description('Pago vencido o suspendidas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($tiendasProblema > 0 ? 'danger' : 'success'),

            Stat::make('Por Renovar (30 días)', $tiendas30Dias)
                ->description('Suscripciones próximas a vencer')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($tiendas30Dias > 0 ? 'warning' : 'success'),

            Stat::make('Total Usuarios', $totalUsuarios)
                ->description('Registrados en la plataforma')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}

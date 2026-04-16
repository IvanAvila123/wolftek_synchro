<?php

namespace App\Filament\Widgets;

use App\Models\CreditPayment;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('widget_StatsOverview') ?? false;
    }

    protected ?string $heading = 'Resumen del Día';
    
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $storeId = filament()->getTenant()->id;
        $hoy = Carbon::today();

        // 1 calculamos las ventas hechas estrictamente hoy
        $ventasHoy = Sale::where('store_id', $storeId)
            ->whereDate('created_at', $hoy)
            ->sum('total');

        // 2 calculamos abonos recibidos hoy
        $abonosHoy = CreditPayment::where('store_id', $storeId)
            ->whereDate('created_at', $hoy)
            ->sum('amount');

        // 3 suma total de dinero movido hoy (ventas + abonos)

        $ingresoTotal = $ventasHoy + $abonosHoy;

        return [
            Stat::make('Ventas Hoy', '$' . number_format($ventasHoy, 2))
                ->description('Total vendido en Punto de Venta')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success')
                // Le ponemos una pequeña gráfica de adorno (pueden ser datos reales después)
                ->chart([2, 3, 5, 4, 7, 5, 8]),
            Stat::make('Abonos Hoy', '$' . number_format($abonosHoy, 2))
                ->description('Total de abonos recibidos')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info')
                ->chart([1, 0, 2, 1, 4, 2, 3]),
            Stat::make('Ingreso Total del dia', '$' . number_format($ingresoTotal, 2))
                ->description('Ventas + Abonos recibidos')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->chart([4, 6, 5, 8, 7, 9, 12]),
        ];
    }
}

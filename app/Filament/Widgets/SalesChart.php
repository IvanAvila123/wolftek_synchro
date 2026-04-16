<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SalesChart extends ChartWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner', 'manager'])) {
            return true;
        }
        return $user?->can('widget_SalesChart') ?? false;
    }

    protected ?string $heading = 'Ventas de los últimos 7 días';
    
    // Le decimos que ocupe todo el ancho de la pantalla
    protected int | string | array $columnSpan = 'full';
    
    // Lo ponemos en la posición 2 (debajo de las tarjetas de resumen)
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $dataFisico = [];
        $dataOnline = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('d M'); // Ej: 05 Abr

            // 1. Dinero total en la caja (Físico + Online ya cobrado)
            // Cambia '\App\Models\Sale' por el nombre correcto de tu modelo si es diferente
            $ventasTotalesCaja = \App\Models\Sale::where('store_id', filament()->getTenant()->id)
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');

            // 2. Dinero exclusivo de pedidos en línea
            $ventasOnline = \App\Models\OnlineOrder::where('store_id', filament()->getTenant()->id)
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');

            // 3. Separamos para que la gráfica no duplique números
            $ventaPuramenteFisica = $ventasTotalesCaja - $ventasOnline;

            // Guardamos los datos del día
            $dataFisico[] = $ventaPuramenteFisica > 0 ? $ventaPuramenteFisica : 0;
            $dataOnline[] = $ventasOnline;
        }

        return [
            'datasets' => [
                [
                    // 👇 MAGIA: Le decimos que este dataset específico es una LÍNEA
                    'type' => 'line', 
                    'label' => 'Ventas en Línea ($)',
                    'data' => $dataOnline,
                    'borderColor' => '#3b82f6', // Azul
                    'backgroundColor' => '#3b82f6',
                    'tension' => 0.4, // Curva suave
                    'borderWidth' => 3,
                    'fill' => false,
                ],
                [
                    // 👇 MAGIA: Le decimos que este dataset específico es de BARRAS
                    'type' => 'bar', 
                    'label' => 'Ventas Mostrador ($)',
                    'data' => $dataFisico,
                    'backgroundColor' => '#4ade80', // Tu verde original
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // El tipo base del widget debe ser 'bar' para que soporte la mezcla
        return 'bar';
    }
}
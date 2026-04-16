<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class UpgradePlan extends Page
{
    protected string $view = 'filament.pages.upgrade-plan';

    // No aparece en el menú de navegación
    protected static bool $shouldRegisterNavigation = false;

    public Plan $currentPlan;
    public string $currentPlanName;
    public array $plans = [];
    public string $blockedFeatureLabel = '';

    public function mount(?string $feature = null): void
    {
        $store = Filament::getTenant();

        $this->currentPlan     = $store->plan;
        $this->currentPlanName = $store->plan?->name ?? 'Sin plan';

        $featureLabels = [
            'suppliers' => 'Proveedores y Compras',
            'customers' => 'Clientes y Fiado',
            'batches'   => 'Lotes y Caducidades',
            'expenses'  => 'Control de Gastos',
        ];

        $this->blockedFeatureLabel = $featureLabels[$feature] ?? 'esta función';

        $this->plans = Plan::orderBy('price')->get()->map(fn (Plan $plan) => [
            'id'           => $plan->id,
            'name'         => $plan->name,
            'price'        => $plan->price,
            'max_users'    => $plan->max_users === -1 ? 'Ilimitados' : "Hasta {$plan->max_users}",
            'max_branches' => $plan->max_branches === -1 ? 'Ilimitadas' : "{$plan->max_branches}",
            'features'     => $plan->features ?? [],
            'is_current'   => $plan->id === $store->plan_id,
        ])->toArray();
    }

    public function getTitle(): string
    {
        return 'Mejora tu Plan';
    }
}

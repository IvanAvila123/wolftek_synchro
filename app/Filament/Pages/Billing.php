<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Billing extends Page
{
    protected string $view = 'filament.pages.billing';

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user?->hasDirectRole(['owner'])) {
            return true;
        }
        return $user?->can('page_Billing') ?? false;
    }
    
    protected static ?string $navegationIcon = 'heroicon-o-credit-card';
    protected static string|UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $title = 'Mi Suscripción';

    public $plans;
    public $currentPlanId;
    public $currentPlanName;
    public $currentPlanPrice;
    public $storeStatus;
    public $validUntil;
    public $daysLeft;

    public function mount()
    {
        /** @var User $user */
        $user  = Auth::user();
        $store = $user->store;
 
        $this->plans = Plan::all();
 
        if ($store) {
            $this->currentPlanId    = $store->plan_id;
            $this->currentPlanName  = $store->plan?->name ?? 'Sin plan';
            $this->currentPlanPrice = $store->plan?->price ?? 0;
            $this->storeStatus      = $store->estatus;
 
            $rawDate = $store->estatus === 'trial' && $store->trial_ends_at
                ? $store->trial_ends_at
                : $store->valid_until;
 
            $this->validUntil = $rawDate ? Carbon::parse($rawDate) : null;
            $this->daysLeft   = $this->validUntil ? (int) now()->diffInDays($this->validUntil, false) : null;
        }
    }
 
    public function subscribe($planId)
    {
        /** @var User $user */
        $user  = Auth::user();
        $store = $user->store;
        $plan  = Plan::find($planId);

        if (!$store || !$plan) {
            return;
        }

        // 👇 AQUI PONES TUS LINKS DE MERCADO PAGO 👇
        // Pon el nombre EXACTO de tu plan tal como está en tu base de datos (ej. "Basico", "Profesional")
        $mercadoPagoLinks = [
            'Basico' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=944c0a40a3a94337bc81560bd3a852d5',
            'Profesional' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=0e93600892ad4834aeb5c472f193f36f',
            'Empresarial' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=336d02e2e46e49c39b387c1233cae730',
        ];

        // Buscamos el link correspondiente al plan que eligió el cliente
        $urlDestino = $mercadoPagoLinks[$plan->name] ?? null;

        if ($urlDestino) {
            // Agregamos external_reference para identificar la tienda en el webhook
            $urlDestino .= '&external_reference=store_' . $store->id . '_plan_' . $plan->id;
            return redirect()->away($urlDestino);
        } else {
            // Si no encuentra el link (por si el nombre del plan no coincide)
            Notification::make()
                ->title('Error de enlace')
                ->body('No se encontró el link de pago para el plan: ' . $plan->name)
                ->danger()
                ->send();
        }
    }
}
<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Conekta\Configuration;
use Conekta\Api\OrdersApi;
use Conekta\Model\OrderRequest;

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

    // Variables que pasaremos a la vista
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
            $this->currentPlanId   = $store->plan_id;
            $this->currentPlanName = $store->plan?->name ?? 'Sin plan';
            $this->currentPlanPrice = $store->plan?->price ?? 0;
            $this->storeStatus     = $store->estatus;
 
            $rawDate = $store->estatus === 'trial' && $store->trial_ends_at
                ? $store->trial_ends_at
                : $store->valid_until;
 
            // Asegurar que sea Carbon
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

        // 1. Configurar la llave PRIVADA de Conekta
        $config      = Configuration::getDefaultConfiguration()->setAccessToken(config('conekta.private_key'));
        $apiInstance = new OrdersApi(null, $config);

        // --- EL SALVAVIDAS DEL NOMBRE ---
        $rawName = $user->name;

        // 1. Quitamos cualquier número o símbolo (Solo dejamos letras y espacios)
        $cleanName = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/u', '', $rawName);
        
        // 2. Quitamos espacios dobles o vacíos a los lados
        $cleanName = trim(preg_replace('/\s+/', ' ', $cleanName));

        // 3. Verificamos que haya sobrevivido algo y que tenga al menos dos palabras
        $nameParts = explode(' ', $cleanName);
        if (empty($cleanName)) {
            $customerName = 'Cliente Sistema'; // Si su nombre era puro número (ej. "1234")
        } elseif (count($nameParts) < 2) {
            $customerName = $nameParts[0] . ' Frecuente'; // Si solo tenía una palabra válida
        } else {
            $customerName = $cleanName; // Si es un nombre perfecto (ej. "Ivan Avila")
        }
        // --------------------------------

        try {
            // 2. Le pedimos a Conekta generar una Orden
            $orderRequest = new OrderRequest([
                'currency' => 'MXN',
                'customer_info' => [
                    'name' => $customerName, // Usamos nuestra variable segura
                    'email' => $user->email,
                    'phone' => $user->phone ?? '5555555555'
                ],
                'line_items' => [
                    [
                        'name' => 'Plan ' . $plan->name . ' (1 Mes)',
                        'unit_price' => $plan->price * 100, // En centavos
                        'quantity' => 1
                    ]
                ],
                'checkout' => [
                    'allowed_payment_methods' => ["card", "cash", "bank_transfer"],
                    'type' => 'HostedPayment',
                    'success_url' => static::getUrl() . '?status=success',
                    'failure_url' => static::getUrl() . '?status=cancelled'
                ],
                'metadata' => [
                    'store_id' => $store->id,
                    'plan_id' => $plan->id
                ]
            ]);

            // 3. Crear la orden
            $response = $apiInstance->createOrder($orderRequest, "es");
            
            // 4. Extraer la URL
            $checkoutUrl = $response->getCheckout()->getUrl();

            // 5. Redirigir
            return redirect()->away($checkoutUrl);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error de conexión')
                ->body('Hubo un problema al conectar con la pasarela de pagos: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}

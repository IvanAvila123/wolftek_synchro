<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
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

    // ─── Estado de suscripción ────────────────────────────────────────────────
    public $plans;
    public $currentPlanId;
    public $currentPlanName;
    public $currentPlanPrice;
    public $storeStatus;
    public $validUntil;
    public $daysLeft;

    // ─── Método de pago seleccionado ──────────────────────────────────────────
    public string $selectedPaymentMethod = 'mercadopago'; // 'mercadopago' | 'transfer'

    // ─── Modal de transferencia ───────────────────────────────────────────────
    public bool   $transferModalOpen = false;
    public ?int   $selectedPlanId    = null;
    public string $transferReference = '';
    public string $transferNotes     = '';

    // ─── Historial de pagos ───────────────────────────────────────────────────
    public array  $paymentHistory  = [];
    public ?array $pendingPayment  = null;

    // ─── Datos bancarios para SPEI ────────────────────────────────────────────
    // ⚙️ Cambia estos valores con tu información bancaria real
    public string $bankClabe        = '638180000056813478';
    public string $bankNombre       = 'NU';
    public string $bankBeneficiario = 'Jesus Ivan Avila Ramirez';

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

        $this->loadPaymentData();
    }

    private function loadPaymentData(): void
    {
        $store = Auth::user()->store;
        if (!$store) return;

        $this->paymentHistory = SubscriptionPayment::where('store_id', $store->id)
            ->with('plan')
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'plan_name'      => $p->plan->name,
                'amount'         => $p->amount,
                'months'         => $p->months,
                'payment_method' => $p->payment_method,
                'status'         => $p->status,
                'reference'      => $p->reference,
                'admin_notes'    => $p->admin_notes,
                'created_at'     => $p->created_at->format('d/m/Y H:i'),
                'approved_at'    => $p->approved_at?->format('d/m/Y'),
            ])
            ->toArray();

        $pending = SubscriptionPayment::where('store_id', $store->id)
            ->where('status', 'pending')
            ->with('plan')
            ->latest()
            ->first();

        $this->pendingPayment = $pending ? [
            'plan_name'  => $pending->plan->name,
            'amount'     => $pending->amount,
            'reference'  => $pending->reference,
            'created_at' => $pending->created_at->format('d/m/Y H:i'),
        ] : null;
    }

    public function subscribe($planId)
    {
        if ($this->selectedPaymentMethod === 'transfer') {
            $this->selectedPlanId    = $planId;
            $this->transferModalOpen = true;
            $this->transferReference = '';
            $this->transferNotes     = '';
            return;
        }

        // ─── MercadoPago ──────────────────────────────────────────────────────
        /** @var User $user */
        $user  = Auth::user();
        $store = $user->store;
        $plan  = Plan::find($planId);

        if (!$store || !$plan) return;

        // 👇 AQUI PONES TUS LINKS DE MERCADO PAGO 👇
        $mercadoPagoLinks = [
            'Basico'       => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=944c0a40a3a94337bc81560bd3a852d5',
            'Profesional'  => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=0e93600892ad4834aeb5c472f193f36f',
            'Empresarial'  => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_plan_id=336d02e2e46e49c39b387c1233cae730',
        ];

        $urlDestino = $mercadoPagoLinks[$plan->name] ?? null;

        if ($urlDestino) {
            $urlDestino .= '&external_reference=store_' . $store->id . '_plan_' . $plan->id;
            return redirect()->away($urlDestino);
        }

        Notification::make()
            ->title('Error de enlace')
            ->body('No se encontró el link de pago para el plan: ' . $plan->name)
            ->danger()
            ->send();
    }

    public function submitTransfer(): void
    {
        if (empty($this->transferReference)) {
            Notification::make()
                ->title('Ingresa el número de referencia')
                ->body('El folio o número de referencia de la transferencia es obligatorio.')
                ->warning()
                ->send();
            return;
        }

        /** @var User $user */
        $user  = Auth::user();
        $store = $user->store;
        $plan  = Plan::find($this->selectedPlanId);

        if (!$store || !$plan) return;

        // No permitir dos pagos pendientes al mismo tiempo
        if (SubscriptionPayment::where('store_id', $store->id)->where('status', 'pending')->exists()) {
            Notification::make()
                ->title('Ya tienes un pago en revisión')
                ->body('Espera a que se apruebe tu pago anterior antes de enviar otro.')
                ->warning()
                ->send();
            return;
        }

        SubscriptionPayment::create([
            'store_id'       => $store->id,
            'plan_id'        => $plan->id,
            'amount'         => $plan->price,
            'months'         => 1,
            'payment_method' => 'transfer',
            'status'         => 'pending',
            'reference'      => trim($this->transferReference),
            'notes'          => trim($this->transferNotes) ?: null,
        ]);

        // Notificar a todos los superadmins
        $superadmins = User::role('super_admin')->get();
        foreach ($superadmins as $admin) {
            Notification::make()
                ->title('Nueva solicitud de transferencia')
                ->body("{$store->name} envió comprobante para el plan {$plan->name} — $" . number_format($plan->price, 2))
                ->warning()
                ->sendToDatabase($admin);
        }

        $this->transferModalOpen = false;
        $this->selectedPlanId    = null;
        $this->transferReference = '';
        $this->transferNotes     = '';

        $this->loadPaymentData();

        Notification::make()
            ->title('¡Comprobante enviado!')
            ->body('Tu pago está en revisión. Te avisaremos cuando sea aprobado.')
            ->success()
            ->send();
    }

    public function cancelTransfer(): void
    {
        $this->transferModalOpen = false;
        $this->selectedPlanId    = null;
        $this->transferReference = '';
        $this->transferNotes     = '';
    }
}

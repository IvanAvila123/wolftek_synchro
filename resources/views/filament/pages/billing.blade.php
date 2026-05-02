<x-filament-panels::page>
    <div class="space-y-8">

        {{-- ═══════════════════════════════════════════════════
             BANNER DE ESTADO ACTUAL
        ═══════════════════════════════════════════════════ --}}
        <div class="relative overflow-hidden rounded-xl
            @if($storeStatus === 'active') bg-gradient-to-r from-emerald-600 to-teal-600
            @elseif($storeStatus === 'trial') bg-gradient-to-r from-amber-500 to-orange-500
            @else bg-gradient-to-r from-red-600 to-rose-600
            @endif
            p-6 text-white shadow-lg">

            <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute -right-2 bottom-0 h-20 w-20 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-white/70">Tu plan actual</p>
                    <h2 class="mt-1 text-2xl font-extrabold">{{ $currentPlanName }}</h2>

                    @if($storeStatus === 'trial')
                        <p class="mt-2 flex items-center gap-2 text-sm text-white/80">
                            <span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-bold">
                                🧪 PRUEBA GRATIS
                            </span>
                            @if($daysLeft !== null && $daysLeft > 0)
                                Te quedan <span class="font-bold text-white">{{ $daysLeft }} días</span>
                            @elseif($daysLeft !== null && $daysLeft <= 0)
                                <span class="font-bold">Tu prueba ha expirado</span>
                            @endif
                        </p>
                    @elseif($storeStatus === 'active')
                        <p class="mt-2 text-sm text-white/80">
                            ✅ Suscripción activa
                            @if($validUntil)
                                · Próximo pago: <span class="font-bold text-white">{{ $validUntil->format('d/M/Y') }}</span>
                            @endif
                        </p>
                    @else
                        <p class="mt-2 text-sm text-white/90">
                            ⛔ Tu cuenta está suspendida. Elige un plan para reactivar.
                        </p>
                    @endif
                </div>

                @if($currentPlanPrice)
                    <div class="text-right">
                        <p class="text-sm text-white/60">Pago mensual</p>
                        <p class="text-3xl font-extrabold">${{ number_format($currentPlanPrice, 2) }}</p>
                        <p class="text-xs text-white/50">MXN / mes</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             BANNER: PAGO PENDIENTE DE APROBACIÓN
        ═══════════════════════════════════════════════════ --}}
        @if($pendingPayment)
            <div class="flex items-start gap-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                    <x-heroicon-o-clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-amber-800 dark:text-amber-300">Pago en revisión</p>
                    <p class="mt-0.5 text-sm text-amber-700 dark:text-amber-400">
                        Tu transferencia para el plan <strong>{{ $pendingPayment['plan_name'] }}</strong>
                        (${{ number_format($pendingPayment['amount'], 2) }}) está siendo verificada.
                        Referencia: <strong>{{ $pendingPayment['reference'] }}</strong>
                    </p>
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-500">Enviado el {{ $pendingPayment['created_at'] }}</p>
                </div>
                <span class="shrink-0 rounded-full bg-amber-100 dark:bg-amber-500/20 px-3 py-1 text-xs font-bold text-amber-700 dark:text-amber-300">
                    En revisión
                </span>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════
             SELECTOR DE MÉTODO DE PAGO
        ═══════════════════════════════════════════════════ --}}
        <div>
            <h2 class="mb-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                ¿Cómo quieres pagar?
            </h2>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                Elige tu método de pago preferido antes de seleccionar un plan.
            </p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {{-- MercadoPago --}}
                <button
                    wire:click="$set('selectedPaymentMethod', 'mercadopago')"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-150
                        {{ $selectedPaymentMethod === 'mercadopago'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 shadow-sm shadow-blue-500/10'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-blue-300 dark:hover:border-blue-600' }}"
                >
                    @if($selectedPaymentMethod === 'mercadopago')
                        <div class="absolute top-3 right-3 h-2.5 w-2.5 rounded-full bg-blue-500 animate-pulse"></div>
                    @endif
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-500/20">
                        <x-heroicon-o-credit-card class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">MercadoPago</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tarjeta de crédito/débito · Pago automático mensual</p>
                    </div>
                </button>

                {{-- Transferencia SPEI --}}
                <button
                    wire:click="$set('selectedPaymentMethod', 'transfer')"
                    class="group relative flex items-center gap-4 rounded-xl border-2 p-4 text-left transition-all duration-150
                        {{ $selectedPaymentMethod === 'transfer'
                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10 shadow-sm shadow-violet-500/10'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-violet-300 dark:hover:border-violet-600' }}"
                >
                    @if($selectedPaymentMethod === 'transfer')
                        <div class="absolute top-3 right-3 h-2.5 w-2.5 rounded-full bg-violet-500 animate-pulse"></div>
                    @endif
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-500/20">
                        <x-heroicon-o-building-library class="h-6 w-6 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Transferencia bancaria (SPEI)</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Transfiere a nuestra CLABE · Aprobación en 24 hrs</p>
                    </div>
                </button>
            </div>

            {{-- Datos bancarios (solo si seleccionó transferencia) --}}
            @if($selectedPaymentMethod === 'transfer')
                <div class="mt-4 rounded-xl bg-violet-50 dark:bg-violet-500/10 ring-1 ring-violet-200 dark:ring-violet-500/30 p-5 space-y-3">
                    <div class="flex items-center gap-2 mb-1">
                        <x-heroicon-o-information-circle class="h-4 w-4 text-violet-600 dark:text-violet-400" />
                        <span class="text-sm font-bold text-violet-700 dark:text-violet-300">Datos para tu transferencia SPEI</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-white dark:bg-gray-900 p-3 ring-1 ring-violet-100 dark:ring-violet-500/20">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-500 dark:text-violet-400 mb-1">CLABE interbancaria</p>
                            <p class="font-bold text-gray-900 dark:text-white font-mono text-sm tracking-widest">{{ $bankClabe }}</p>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-900 p-3 ring-1 ring-violet-100 dark:ring-violet-500/20">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-500 dark:text-violet-400 mb-1">Banco</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $bankNombre }}</p>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-900 p-3 ring-1 ring-violet-100 dark:ring-violet-500/20">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-500 dark:text-violet-400 mb-1">Beneficiario</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $bankBeneficiario }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-violet-600 dark:text-violet-400 flex items-start gap-1.5">
                        <x-heroicon-o-light-bulb class="h-3.5 w-3.5 shrink-0 mt-0.5" />
                        Después de transferir, elige tu plan y envía el folio de confirmación. Lo revisamos en máximo 24 horas hábiles.
                    </p>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════
             GRID DE PLANES
        ═══════════════════════════════════════════════════ --}}
        <div>
            <h2 class="mb-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                Elige tu plan
            </h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Todos los planes incluyen acceso completo al sistema. Cancela cuando quieras.
            </p>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $currentPlanId === $plan->id && $storeStatus === 'active';
                    @endphp

                    <div class="relative flex flex-col overflow-hidden rounded-xl border-2 transition-all duration-200
                        {{ $isCurrent
                            ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/5 shadow-lg shadow-primary-500/10'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md'
                        }}">

                        @if($isCurrent)
                            <div class="bg-primary-500 px-4 py-1.5 text-center text-xs font-bold uppercase tracking-wider text-white">
                                ✓ Tu plan actual
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>

                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                    ${{ number_format($plan->price, 0) }}
                                </span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">/mes</span>
                            </div>

                            <ul class="mt-6 flex-1 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-center gap-2.5">
                                    <x-filament::icon icon="heroicon-o-users" class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />
                                    @if($plan->max_users >= 999)
                                        <span class="font-medium">Usuarios ilimitados</span>
                                    @else
                                        Hasta <span class="font-medium">{{ $plan->max_users }} usuarios</span>
                                    @endif
                                </li>

                                <li class="flex items-center gap-2.5">
                                    <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />
                                    @if($plan->max_branches >= 999)
                                        <span class="font-medium">Sucursales ilimitadas</span>
                                    @else
                                        <span class="font-medium">{{ $plan->max_branches }}</span> sucursal(es)
                                    @endif
                                </li>

                                <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

                                @if(is_array($plan->features) || is_object($plan->features))
                                    @php
                                        $nombresCaracteristicas = [
                                            'pos'          => 'Punto de Venta',
                                            'inventory'    => 'Inventario básico',
                                            'suppliers'    => 'Proveedores',
                                            'customers'    => 'Clientes y fiado',
                                            'scale'        => 'Báscula de precio',
                                            'labels'       => 'Etiquetador',
                                            'batches'      => 'Lotes y caducidades',
                                            'whatsapp'     => 'Catalogo en linea',
                                            'reports'      => 'Reportes avanzados',
                                            'multi_branch' => 'Multi-sucursal',
                                            'expenses'     => 'Control de gastos',
                                            'loyalty'      => 'Programa de lealtad',
                                            'api'          => 'Acceso API',
                                        ];
                                    @endphp
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-center gap-2.5">
                                            <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 shrink-0 text-emerald-500" />
                                            {{ $nombresCaracteristicas[$feature] ?? ucfirst(str_replace('_', ' ', $feature)) }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <div class="mt-6">
                                @if($isCurrent)
                                    <x-filament::button color="gray" disabled class="w-full justify-center">
                                        Plan Actual
                                    </x-filament::button>
                                @elseif($pendingPayment)
                                    <x-filament::button color="warning" disabled class="w-full justify-center">
                                        Pago en revisión
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        wire:click="subscribe({{ $plan->id }})"
                                        :color="$selectedPaymentMethod === 'transfer' ? 'secondary' : 'primary'"
                                        class="w-full justify-center"
                                        size="lg"
                                    >
                                        @if($selectedPaymentMethod === 'transfer')
                                            🏦 Pagar con transferencia
                                        @elseif($storeStatus === 'trial')
                                            Activar Plan
                                        @elseif($currentPlanId === $plan->id)
                                            Renovar Plan
                                        @else
                                            Elegir Plan
                                        @endif
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             HISTORIAL DE PAGOS
        ═══════════════════════════════════════════════════ --}}
        @if(!empty($paymentHistory))
            <div>
                <h2 class="mb-4 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Historial de pagos
                </h2>

                <div class="overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/60">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Plan</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Método</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Monto</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Referencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                            @foreach($paymentHistory as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $payment['created_at'] }}
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $payment['plan_name'] }}
                                        @if($payment['months'] > 1)
                                            <span class="ml-1 text-xs text-gray-400">({{ $payment['months'] }} meses)</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($payment['payment_method'] === 'transfer')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 dark:bg-violet-500/20 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:text-violet-300">
                                                🏦 Transferencia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 dark:bg-blue-500/20 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300">
                                                💳 MercadoPago
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                        ${{ number_format($payment['amount'], 2) }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($payment['status'] === 'approved')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                                ✓ Aprobado
                                            </span>
                                        @elseif($payment['status'] === 'pending')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/20 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-300">
                                                ⏳ En revisión
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/20 px-2.5 py-1 text-xs font-bold text-red-700 dark:text-red-300" title="{{ $payment['admin_notes'] ?? '' }}">
                                                ✗ Rechazado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $payment['reference'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════
             INFO DE CONTACTO
        ═══════════════════════════════════════════════════ --}}
        <x-filament::section>
            <div class="flex flex-col items-center gap-2 py-4 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    ¿Tienes dudas sobre tu suscripción? ¿Necesitas factura?
                </p>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    📧 soporte@wolftek.com &nbsp;·&nbsp; 📱 WhatsApp: 55 1234 5678
                </p>
            </div>
        </x-filament::section>

    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL: Formulario de transferencia
    ═══════════════════════════════════════════════════ --}}
    @if($transferModalOpen && $selectedPlanId)
        @php $planSeleccionado = $plans->firstWhere('id', $selectedPlanId); @endphp
        @if($planSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" wire:click.self="cancelTransfer">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden ring-1 ring-gray-200 dark:ring-white/10">

                {{-- Header --}}
                <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirmar pago por transferencia</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Plan {{ $planSeleccionado->name }} — ${{ number_format($planSeleccionado->price, 2) }}/mes
                        </p>
                    </div>
                    <button wire:click="cancelTransfer" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Cuerpo --}}
                <div class="px-6 py-5 space-y-5">

                    {{-- Recordatorio de datos bancarios --}}
                    <div class="rounded-xl bg-violet-50 dark:bg-violet-500/10 ring-1 ring-violet-200 dark:ring-violet-500/20 p-4 space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400">Transfiere exactamente</p>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black text-violet-700 dark:text-violet-300">${{ number_format($planSeleccionado->price, 2) }}</span>
                            <span class="text-sm text-violet-500 dark:text-violet-400">MXN</span>
                        </div>
                        <div class="pt-2 border-t border-violet-200 dark:border-violet-500/20 space-y-1">
                            <p class="text-xs text-violet-700 dark:text-violet-300">
                                <span class="font-semibold">CLABE:</span>
                                <span class="font-mono tracking-widest ml-1">{{ $bankClabe }}</span>
                            </p>
                            <p class="text-xs text-violet-700 dark:text-violet-300">
                                <span class="font-semibold">Banco:</span> {{ $bankNombre }} &nbsp;·&nbsp;
                                <span class="font-semibold">A nombre de:</span> {{ $bankBeneficiario }}
                            </p>
                        </div>
                    </div>

                    {{-- Referencia --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Folio / número de referencia <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model.live="transferReference"
                            class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-violet-500 transition-all font-mono"
                            placeholder="Ej. 202600123456789"
                        >
                        <p class="mt-1 text-xs text-gray-400">El folio aparece en tu comprobante de transferencia bancaria.</p>
                    </div>

                    {{-- Notas opcionales --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Notas adicionales <span class="text-gray-400 font-normal">(opcional)</span>
                        </label>
                        <textarea
                            wire:model.live="transferNotes"
                            rows="2"
                            class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-violet-500 transition-all resize-none"
                            placeholder="Cualquier información adicional sobre tu pago..."
                        ></textarea>
                    </div>
                </div>

                {{-- Pie --}}
                <div class="px-6 pb-6 flex gap-3">
                    <button
                        wire:click="cancelTransfer"
                        class="flex-1 py-3 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-400 ring-1 ring-gray-200 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="submitTransfer"
                        wire:loading.attr="disabled"
                        class="flex-1 py-3 rounded-xl text-sm font-bold text-white bg-violet-500 hover:bg-violet-600 disabled:opacity-60 transition-all shadow-sm shadow-violet-500/20"
                    >
                        <span wire:loading.remove wire:target="submitTransfer">Enviar comprobante</span>
                        <span wire:loading wire:target="submitTransfer">Enviando...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

</x-filament-panels::page>

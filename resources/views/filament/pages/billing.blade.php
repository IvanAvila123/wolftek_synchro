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

            {{-- Círculo decorativo --}}
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

                        {{-- Badge plan actual --}}
                        @if($isCurrent)
                            <div class="bg-primary-500 px-4 py-1.5 text-center text-xs font-bold uppercase tracking-wider text-white">
                                ✓ Tu plan actual
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-6">
                            {{-- Nombre --}}
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $plan->name }}
                            </h3>

                            {{-- Precio --}}
                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                    ${{ number_format($plan->price, 0) }}
                                </span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">/mes</span>
                            </div>

                            {{-- Features (AHORA DINÁMICO) --}}
                            <ul class="mt-6 flex-1 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                
                                {{-- 1. Límite de Usuarios --}}
                                <li class="flex items-center gap-2.5">
                                    <x-filament::icon icon="heroicon-o-users" class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />
                                    @if($plan->max_users >= 999)
                                        <span class="font-medium">Usuarios ilimitados</span>
                                    @else
                                        Hasta <span class="font-medium">{{ $plan->max_users }} usuarios</span>
                                    @endif
                                </li>

                                {{-- 2. Límite de Sucursales --}}
                                <li class="flex items-center gap-2.5">
                                    <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 shrink-0 text-gray-500 dark:text-gray-400" />
                                    @if($plan->max_branches >= 999)
                                        <span class="font-medium">Sucursales ilimitadas</span>
                                    @else
                                        <span class="font-medium">{{ $plan->max_branches }}</span> sucursal(es)
                                    @endif
                                </li>

                                <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

                                {{-- 3. Características marcadas desde el Superadmin --}}
                                @if(is_array($plan->features) || is_object($plan->features))
                                    @php
                                        // 📘 DICCIONARIO DE TRADUCCIÓN
                                        // Aquí mapeas la llave de la base de datos con cómo quieres que se lea.
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
                                            // ➕ Agrega las nuevas llaves aquí abajo cuando las crees
                                        ];
                                    @endphp

                                    @foreach($plan->features as $feature)
                                        <li class="flex items-center gap-2.5">
                                            <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 shrink-0 text-emerald-500" />
                                            {{-- Busca en el diccionario. Si no lo encuentra, imprime la llave normal --}}
                                            {{ $nombresCaracteristicas[$feature] ?? ucfirst(str_replace('_', ' ', $feature)) }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            {{-- Botón --}}
                            <div class="mt-6">
                                @if($isCurrent)
                                    <x-filament::button color="gray" disabled class="w-full justify-center">
                                        Plan Actual
                                    </x-filament::button>
                                @else
                                    <x-filament::button
                                        wire:click="subscribe({{ $plan->id }})"
                                        class="w-full justify-center"
                                        size="lg"
                                    >
                                        @if($storeStatus === 'trial')
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
</x-filament-panels::page>
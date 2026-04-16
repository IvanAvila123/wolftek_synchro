<x-filament-panels::page>
    <div class="flex flex-col items-center gap-10 py-6">

        {{-- Encabezado --}}
        <div class="flex flex-col items-center gap-3 text-center">
            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-warning-100 dark:bg-warning-900/30">
                <x-heroicon-o-lock-closed class="w-10 h-10 text-warning-500" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Esta función no está incluida en tu plan
            </h2>
            <p class="max-w-lg text-base text-gray-500 dark:text-gray-400">
                <strong class="text-gray-700 dark:text-gray-200">{{ ucfirst($blockedFeatureLabel) }}</strong>
                está disponible en planes superiores. Actualiza tu plan para desbloquear esta y más funciones.
            </p>
        </div>

        {{-- Tarjetas de planes --}}
        <div class="grid w-full max-w-5xl grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $isCurrent   = $plan['is_current'];
                    $featureMap  = [
                        'pos'              => 'Punto de Venta',
                        'inventory'  => 'Inventario básico',
                        'suppliers'        => 'Proveedores y Compras',
                        'customers'        => 'Clientes y Fiado',
                        'batches'          => 'Lotes y Caducidades',
                        'expenses'         => 'Control de Gastos',
                        'labels'           => 'Etiquetador',
                        'scale'            => 'Báscula de precio',
                        'whatsapp'         => 'Catálogo en línea',
                        'reports'          => 'Reportes avanzados',
                        'multi_branch'     => 'Multi-sucursal',
                        'loyalty'          => 'Programa de lealtad',
                        'api'       => 'Acceso API',
                    ];
                @endphp

                <div @class([
                    'relative flex flex-col rounded-2xl border p-6 shadow-sm transition-all',
                    'border-primary-500 ring-2 ring-primary-500 dark:border-primary-400 dark:ring-primary-400'  => $isCurrent,
                    'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600' => ! $isCurrent,
                    'bg-white dark:bg-gray-800' => true,
                ])>
                    {{-- Etiqueta plan actual --}}
                    @if ($isCurrent)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-500 px-3 py-1 text-xs font-semibold text-white">
                                <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                Tu plan actual
                            </span>
                        </div>
                    @endif

                    {{-- Nombre y precio --}}
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $plan['name'] }}
                        </h3>
                        <div class="mt-1 flex items-end gap-1">
                            <span class="text-3xl font-extrabold text-gray-900 dark:text-white">
                                ${{ number_format($plan['price'], 0) }}
                            </span>
                            <span class="mb-1 text-sm text-gray-500 dark:text-gray-400">/mes</span>
                        </div>
                    </div>

                    {{-- Límites --}}
                    <div class="mb-4 flex flex-col gap-1.5 border-b border-gray-100 pb-4 dark:border-gray-700">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
                            {{ $plan['max_users'] }} usuarios
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <x-heroicon-o-building-storefront class="w-4 h-4 text-gray-400" />
                            {{ $plan['max_branches'] }} sucursal(es)
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="mb-6 flex flex-col gap-2">
                        @foreach ($featureMap as $key => $label)
                            @php $hasIt = in_array($key, $plan['features']); @endphp
                            <li class="flex items-center gap-2 text-sm">
                                @if ($hasIt)
                                    <x-heroicon-s-check-circle class="w-4 h-4 flex-shrink-0 text-success-500" />
                                    <span class="text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                @else
                                    <x-heroicon-o-x-circle class="w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600" />
                                    <span class="text-gray-400 dark:text-gray-500 line-through">{{ $label }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        @if ($isCurrent)
                            <div class="flex items-center justify-center rounded-lg border border-gray-200 py-2 text-sm font-medium text-gray-400 dark:border-gray-600 dark:text-gray-500">
                                Plan activo
                            </div>
                        @else
                            <a
                                href="https://wa.me/5634632825?text=Hola,%20quiero%20cambiar%20al%20plan%20{{ urlencode($plan['name']) }}"
                                target="_blank"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                                <x-heroicon-o-arrow-up-circle class="w-4 h-4" />
                                Cambiar a {{ $plan['name'] }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Botón volver --}}
        <div class="flex items-center gap-3">
            <a
                href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Regresar
            </a>
            <p class="text-sm text-gray-400 dark:text-gray-500">
                ¿Dudas? Escríbenos y te ayudamos a elegir.
            </p>
        </div>
    </div>
</x-filament-panels::page>

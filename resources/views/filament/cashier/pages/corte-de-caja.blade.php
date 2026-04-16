<x-filament-panels::page>
    <style>
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .fade-up {
            animation: fadeUp 0.4s ease-out;
        }

        .fade-up-1 {
            animation: fadeUp 0.4s ease-out 0.05s both;
        }

        .fade-up-2 {
            animation: fadeUp 0.4s ease-out 0.1s both;
        }

        .fade-up-3 {
            animation: fadeUp 0.4s ease-out 0.15s both;
        }

        .count-up {
            animation: countUp 0.3s ease-out 0.2s both;
        }
    </style>

    @if($shift)
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- ========== COLUMNA IZQUIERDA: Resumen (3/5) ========== --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- Header del turno --}}
            <div class="fade-up bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg shadow-emerald-500/20">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full border-4 border-white"></div>
                    <div class="absolute -bottom-3 -left-3 w-20 h-20 rounded-full border-4 border-white"></div>
                </div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-semibold uppercase tracking-widest mb-1">Corte de Caja</p>
                        <h2 class="text-2xl font-black tracking-tight">Resumen del Turno</h2>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-xs text-emerald-200 font-medium">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-emerald-200/70">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Efectivo esperado (card destacado) --}}
            <div class="fade-up-1 bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Efectivo en Gaveta</h3>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Fondo inicial</span>
                        <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($resumen['fondo_inicial'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Ventas en efectivo</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">+ ${{ number_format($resumen['ventas_efectivo'], 2) }}</span>
                    </div>

                    @if($resumen['abonos_efectivo'] > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Abonos cobrados (efectivo)</span>
                        <span class="font-semibold text-teal-600 dark:text-teal-400">+ ${{ number_format($resumen['abonos_efectivo'], 2) }}</span>
                    </div>
                    @endif

                    @if($resumen['gastos_efectivo'] > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Salidas / Gastos:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">- ${{ number_format($resumen['gastos_efectivo'], 2) }}</span>
                    </div>
                    @endif

                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Efectivo esperado</span>
                        <span class="count-up text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">${{ number_format($resumen['esperado_en_caja'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Otros métodos de pago --}}
            <div class="fade-up-2 bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Otros Ingresos</h3>
                    <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">No físicos</span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col items-center px-3 py-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 ring-1 ring-blue-100 dark:ring-blue-500/20">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-blue-500 dark:text-blue-400 mb-2" />
                        <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wider mb-1">Tarjeta</span>
                        <span class="text-base font-bold text-blue-700 dark:text-blue-300">${{ number_format($resumen['ventas_tarjeta'], 2) }}</span>
                        @if($resumen['abonos_tarjeta'] > 0)
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-500 dark:text-gray-400 pl-4 text-xs">+ Abonos (Tarjeta):</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400 text-xs">+ ${{ number_format($resumen['abonos_tarjeta'], 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-center px-3 py-4 rounded-xl bg-violet-50 dark:bg-violet-500/10 ring-1 ring-violet-100 dark:ring-violet-500/20">
                        <x-heroicon-o-device-phone-mobile class="w-5 h-5 text-violet-500 dark:text-violet-400 mb-2" />
                        <span class="text-[10px] font-semibold text-violet-600 dark:text-violet-300 uppercase tracking-wider mb-1">Transferencia</span>
                        <span class="text-base font-bold text-violet-700 dark:text-violet-300">${{ number_format($resumen['ventas_transferencia'], 2) }}</span>
                        @if($resumen['abonos_transferencia'] > 0)
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-500 dark:text-gray-400 pl-4 text-xs">+ Abonos (Transfer.):</span>
                            <span class="font-bold text-violet-600 dark:text-violet-400 text-xs">+ ${{ number_format($resumen['abonos_transferencia'], 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-col items-center px-3 py-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-100 dark:ring-amber-500/20">
                        <x-heroicon-o-document-text class="w-5 h-5 text-amber-500 dark:text-amber-400 mb-2" />
                        <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-300 uppercase tracking-wider mb-1">Crédito</span>
                        <span class="text-base font-bold text-amber-700 dark:text-amber-300">${{ number_format($resumen['ventas_credito'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Venta total --}}
            <div class="fade-up-3 bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm px-5 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <x-heroicon-o-chart-bar class="w-4.5 h-4.5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Venta Total del Día</span>
                    </div>
                    <span class="text-xl font-black text-gray-900 dark:text-white">${{ number_format($resumen['total_ventas'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- ========== COLUMNA DERECHA: Cierre (2/5) ========== --}}
        <div class="lg:col-span-2">
            <div class="fade-up-2 bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm overflow-hidden sticky top-6">

                {{-- Header rojo --}}
                <div class="bg-gradient-to-br from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 px-6 py-5 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full border-4 border-white"></div>
                    </div>
                    <div class="relative flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center ring-1 ring-white/30">
                            <x-heroicon-o-lock-closed class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Cerrar Turno</h2>
                            <p class="text-red-200 text-xs">Finalizar jornada</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Instrucción --}}
                    <div class="flex items-start gap-2.5 mb-5 px-3 py-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-100 dark:ring-gray-800">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Cuenta los <span class="font-semibold text-gray-700 dark:text-gray-300">billetes y monedas</span> que hay en la gaveta. El sistema calculará si hay sobrante o faltante.
                        </p>
                    </div>

                    {{-- Recordatorio del monto esperado --}}
                    <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/20 text-center">
                        <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-0.5">Esperado en gaveta</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">${{ number_format($resumen['esperado_en_caja'], 2) }}</p>
                    </div>

                    {{-- Formulario --}}
                    <form wire:submit="cerrarCaja" class="space-y-5">
                        {{ $this->form }}

                        {{-- Diferencia en tiempo real --}}
                        @if(isset($data['closing_amount']) && is_numeric($data['closing_amount']))
                        @php
                        $diferencia = $data['closing_amount'] - $resumen['esperado_en_caja'];
                        @endphp
                        <div class="px-4 py-3 rounded-xl text-center ring-1
                                    {{ $diferencia == 0
                                        ? 'bg-emerald-50 dark:bg-emerald-500/10 ring-emerald-200 dark:ring-emerald-500/20'
                                        : ($diferencia > 0
                                            ? 'bg-blue-50 dark:bg-blue-500/10 ring-blue-200 dark:ring-blue-500/20'
                                            : 'bg-red-50 dark:bg-red-500/10 ring-red-200 dark:ring-red-500/20') }}">
                            <p class="text-[10px] font-semibold uppercase tracking-widest mb-0.5
                                        {{ $diferencia == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($diferencia > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $diferencia == 0 ? 'Cuadra perfecto' : ($diferencia > 0 ? 'Sobrante' : 'Faltante') }}
                            </p>
                            <p class="text-lg font-bold
                                        {{ $diferencia == 0 ? 'text-emerald-700 dark:text-emerald-300' : ($diferencia > 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-700 dark:text-red-300') }}">
                                {{ $diferencia >= 0 ? '+' : '-' }} ${{ number_format(abs($diferencia), 2) }}
                            </p>
                        </div>
                        @endif

                        <button
                            type="submit"
                            class="w-full flex items-center justify-center gap-2.5 px-5 py-4 rounded-xl text-base font-bold text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900 shadow-lg shadow-red-500/25 hover:shadow-red-500/40 transition-all active:scale-[0.98]">
                            <x-heroicon-o-lock-closed class="w-5 h-5" />
                            Confirmar y Cerrar Caja
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
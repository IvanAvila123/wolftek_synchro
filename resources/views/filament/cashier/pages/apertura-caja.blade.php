<x-filament-panels::page>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .float-icon { animation: float 3s ease-in-out infinite; }
        .fade-up { animation: fadeUp 0.4s ease-out; }
        .fade-up-delay { animation: fadeUp 0.4s ease-out 0.1s both; }
        .fade-up-delay-2 { animation: fadeUp 0.4s ease-out 0.2s both; }
    </style>

    <div class="max-w-lg mx-auto py-8 fade-up">

        {{-- Card principal --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl ring-1 ring-gray-200 dark:ring-white/10 shadow-xl shadow-gray-200/50 dark:shadow-black/20 overflow-hidden">

            {{-- Header visual --}}
            <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 px-8 py-10 text-center overflow-hidden">
                {{-- Decoración de fondo --}}
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full border-4 border-white"></div>
                    <div class="absolute -bottom-4 -left-4 w-24 h-24 rounded-full border-4 border-white"></div>
                    <div class="absolute top-6 left-12 w-8 h-8 rounded-full border-2 border-white"></div>
                </div>

                <div class="relative">
                    <div class="float-icon inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm mb-5 ring-1 ring-white/30">
                        <x-heroicon-o-calculator class="w-8 h-8 text-white" />
                    </div>

                    <h2 class="text-2xl font-black text-white tracking-tight">
                        Apertura de Caja
                    </h2>
                    <p class="text-emerald-100 text-sm mt-2 max-w-xs mx-auto leading-relaxed">
                        Selecciona tu caja e ingresa el monto inicial para comenzar a vender
                    </p>
                </div>
            </div>

            {{-- Estado actual --}}
            <div class="fade-up-delay px-8 -mt-4">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/20">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 shrink-0">
                        <x-heroicon-o-clock class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Turno cerrado</p>
                        <p class="text-xs text-amber-600 dark:text-amber-400/80">Necesitas abrir caja para registrar ventas</p>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="fade-up-delay-2 px-8 py-6">
                <form wire:submit="abrirCaja" class="space-y-5">
                    {{ $this->form }}

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full flex items-center justify-center gap-2.5 px-5 py-4 rounded-xl text-base font-bold text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-900 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all active:scale-[0.98]"
                        >
                            <x-heroicon-o-lock-open class="w-5 h-5" />
                            Abrir Caja y Comenzar a Vender
                        </button>
                    </div>
                </form>
            </div>

            {{-- Footer informativo --}}
            <div class="px-8 pb-6">
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-100 dark:ring-gray-800">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-gray-400" />
                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 text-center leading-tight">Registra el efectivo inicial</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-100 dark:ring-gray-800">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-400" />
                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 text-center leading-tight">Comienza a cobrar ventas</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-100 dark:ring-gray-800">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-400" />
                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 text-center leading-tight">Cierra con corte al final</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info del usuario actual --}}
        <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
            <x-heroicon-o-user-circle class="w-4 h-4" />
            <span>Sesión de <span class="font-semibold text-gray-500 dark:text-gray-400">{{ auth()->user()->name }}</span></span>
            <span class="text-gray-300 dark:text-gray-600">•</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</x-filament-panels::page>
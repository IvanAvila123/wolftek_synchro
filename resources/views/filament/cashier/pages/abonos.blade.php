<x-filament-panels::page>
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.4s ease-out; }
        .fade-up-1 { animation: fadeUp 0.4s ease-out 0.1s both; }
        .slide-in { animation: slideIn 0.2s ease-out; }
    </style>

    <div class="max-w-lg mx-auto py-4 space-y-4">

        {{-- Header --}}
        <div class="fade-up bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg shadow-emerald-500/20">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-6 -right-6 w-28 h-28 rounded-full border-4 border-white"></div>
                <div class="absolute -bottom-3 -left-3 w-20 h-20 rounded-full border-4 border-white"></div>
            </div>
            <div class="relative flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center ring-1 ring-white/30 shrink-0">
                    <x-heroicon-o-banknotes class="w-7 h-7 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-tight">Cobro de Créditos</h2>
                    <p class="text-emerald-100 text-sm mt-0.5">Registrar abonos de clientes con deuda</p>
                </div>
            </div>
        </div>

        {{-- Card principal --}}
        <div class="fade-up-1 bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm overflow-hidden">

            {{-- Selector de cliente --}}
            <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-users class="w-4 h-4 text-gray-400" />
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente con deuda</label>
                </div>
                <select
                    wire:model.live="customerId"
                    class="block w-full rounded-xl border-0 py-3 px-4 text-sm font-medium text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-emerald-500 transition-all"
                >
                    <option value="">-- Selecciona un cliente --</option>
                    @foreach(\App\Models\Customer::where('store_id', filament()->getTenant()->id)->where('balance', '>', 0)->orderBy('name')->get() as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->name }} — Debe: ${{ number_format($cliente->balance, 2) }}</option>
                    @endforeach
                </select>

                @if(\App\Models\Customer::where('store_id', filament()->getTenant()->id)->where('balance', '>', 0)->count() === 0)
                    <div class="flex items-center gap-2 mt-3 px-3 py-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/20">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Todos los clientes están al corriente</p>
                    </div>
                @endif
            </div>

            {{-- Detalle del cliente y formulario --}}
            @if($customerId)
                @php
                    $clienteSeleccionado = \App\Models\Customer::find($customerId);
                @endphp

                @if($clienteSeleccionado)
                    <div class="slide-in border-t border-gray-100 dark:border-gray-800">

                        {{-- Info del cliente --}}
                        <div class="px-5 py-4 bg-gray-50/50 dark:bg-gray-800/30">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-700 dark:text-amber-300 text-base font-bold shrink-0">
                                    {{ strtoupper(substr($clienteSeleccionado->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $clienteSeleccionado->name }}</p>
                                    @if($clienteSeleccionado->phone)
                                        <p class="text-xs text-gray-400 flex items-center gap-1">
                                            <x-heroicon-o-phone class="w-3 h-3" />
                                            {{ $clienteSeleccionado->phone }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Deuda actual destacada --}}
                        <div class="px-5 py-4">
                            <div class="py-4 px-4 rounded-xl bg-red-50 dark:bg-red-500/10 ring-1 ring-red-200 dark:ring-red-500/20 text-center">
                                <p class="text-[10px] font-semibold text-red-500 dark:text-red-400 uppercase tracking-widest mb-1">Deuda pendiente</p>
                                <p class="text-3xl font-black text-red-600 dark:text-red-400 tracking-tight">${{ number_format($clienteSeleccionado->balance, 2) }}</p>
                                @if($clienteSeleccionado->credit_limit > 0)
                                    <p class="text-[10px] text-red-400 dark:text-red-500 mt-1">Límite: ${{ number_format($clienteSeleccionado->credit_limit, 2) }}</p>
                                @endif
                            </div>

                            {{-- Input de monto --}}
                            <div class="mt-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-heroicon-o-currency-dollar class="w-4 h-4 text-emerald-500" />
                                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Monto del abono</label>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500 dark:text-emerald-400 font-bold text-lg">$</span>
                                    <input
                                        type="number"
                                        wire:model.live="monto"
                                        class="block w-full rounded-xl border-0 py-4 pl-9 pr-4 text-2xl font-black text-gray-900 dark:text-white bg-white dark:bg-gray-800 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 placeholder:text-gray-300 dark:placeholder:text-gray-600 focus:ring-2 focus:ring-inset focus:ring-emerald-500 transition-all text-center"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                        max="{{ $clienteSeleccionado->balance }}"
                                    >
                                </div>

                                {{-- Botones de monto rápido --}}
                                <div class="grid grid-cols-3 gap-2 mt-3">
                                    @php
                                        $deuda = $clienteSeleccionado->balance;
                                        $montos = [50, 100, 200];
                                    @endphp
                                    @foreach($montos as $montoRapido)
                                        @if($montoRapido <= $deuda)
                                            <button
                                                wire:click="$set('monto', '{{ $montoRapido }}')"
                                                class="py-2 px-3 rounded-lg text-xs font-bold ring-1 ring-gray-200 dark:ring-gray-700 text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-emerald-50 hover:ring-emerald-300 hover:text-emerald-700 dark:hover:bg-emerald-500/10 dark:hover:ring-emerald-500/30 dark:hover:text-emerald-300 transition-all"
                                            >
                                                ${{ number_format($montoRapido, 0) }}
                                            </button>
                                        @endif
                                    @endforeach
                                    <button
                                        wire:click="$set('monto', '{{ $deuda }}')"
                                        class="py-2 px-3 rounded-lg text-xs font-bold ring-1 ring-emerald-200 dark:ring-emerald-500/30 text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all {{ count(array_filter($montos, fn($m) => $m <= $deuda)) === 3 ? 'col-span-3' : '' }}"
                                    >
                                        Liquidar todo (${{ number_format($deuda, 2) }})
                                    </button>
                                </div>
                            </div>

                            {{-- Método de pago --}}
                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Método de pago</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('paymentMethod', 'cash')" class="pay-btn relative flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center {{ $paymentMethod === 'cash' ? 'bg-emerald-50 dark:bg-emerald-500/15 ring-2 ring-emerald-500 text-emerald-700 dark:text-emerald-300 shadow-sm shadow-emerald-500/10' : 'bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-200 dark:ring-gray-700 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300' }}">
                            @if($paymentMethod === 'cash')<div class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>@endif
                            <x-heroicon-o-banknotes class="w-6 h-6" />
                            <span class="text-xs font-bold">Efectivo</span>
                        </button>

                        <button wire:click="$set('paymentMethod', 'card')" class="pay-btn relative flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center {{ $paymentMethod === 'card' ? 'bg-blue-50 dark:bg-blue-500/15 ring-2 ring-blue-500 text-blue-700 dark:text-blue-300 shadow-sm shadow-blue-500/10' : 'bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-200 dark:ring-gray-700 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300' }}">
                            @if($paymentMethod === 'card')<div class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>@endif
                            <x-heroicon-o-credit-card class="w-6 h-6" />
                            <span class="text-xs font-bold">Tarjeta</span>
                        </button>

                        <button wire:click="$set('paymentMethod', 'transfer')" class="pay-btn relative flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center {{ $paymentMethod === 'transfer' ? 'bg-violet-50 dark:bg-violet-500/15 ring-2 ring-violet-500 text-violet-700 dark:text-violet-300 shadow-sm shadow-violet-500/10' : 'bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-200 dark:ring-gray-700 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300' }}">
                            @if($paymentMethod === 'transfer')<div class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-violet-500 animate-pulse"></div>@endif
                            <x-heroicon-o-device-phone-mobile class="w-6 h-6" />
                            <span class="text-xs font-bold">Transferencia</span>
                        </button>

                    </div>
                </div>

                            {{-- Preview del resultado --}}
                            @if($monto !== '' && is_numeric($monto) && (float)$monto > 0)
                                @php
                                    $montoAbono = min((float)$monto, $clienteSeleccionado->balance);
                                    $nuevaDeuda = $clienteSeleccionado->balance - $montoAbono;
                                @endphp
                                <div class="mt-4 px-4 py-3 rounded-xl ring-1 {{ $nuevaDeuda == 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 ring-emerald-200 dark:ring-emerald-500/20' : 'bg-gray-50 dark:bg-gray-800/50 ring-gray-200 dark:ring-gray-700' }}">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Deuda actual</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($clienteSeleccionado->balance, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm mt-1">
                                        <span class="text-emerald-600 dark:text-emerald-400">Abono</span>
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">- ${{ number_format($montoAbono, 2) }}</span>
                                    </div>
                                    <div class="border-t border-dashed {{ $nuevaDeuda == 0 ? 'border-emerald-200 dark:border-emerald-700' : 'border-gray-200 dark:border-gray-700' }} my-2"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-bold {{ $nuevaDeuda == 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-900 dark:text-white' }}">
                                            {{ $nuevaDeuda == 0 ? 'Deuda liquidada' : 'Nueva deuda' }}
                                        </span>
                                        <span class="text-lg font-black {{ $nuevaDeuda == 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                            ${{ number_format($nuevaDeuda, 2) }}
                                        </span>
                                    </div>
                                </div>

                                @if((float)$monto > $clienteSeleccionado->balance)
                                    <div class="flex items-center gap-2 mt-3 px-3 py-2.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/20">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 shrink-0" />
                                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                            El monto excede la deuda. Solo se abonarán ${{ number_format($clienteSeleccionado->balance, 2) }}
                                        </p>
                                    </div>
                                @endif
                            @endif

                            {{-- Botón de acción --}}
                            <div class="mt-5">
                                <button
                                    wire:click="procesarAbono"
                                    @if(empty($monto) || !is_numeric($monto) || (float)$monto <= 0) disabled @endif
                                    class="w-full flex items-center justify-center gap-2.5 px-5 py-4 rounded-xl text-base font-bold text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-900 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none transition-all active:scale-[0.98]"
                                >
                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                    Registrar Abono
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                {{-- Estado vacío --}}
                <div class="px-5 pb-6">
                    <div class="flex flex-col items-center py-8 px-4">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-user-group class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Selecciona un cliente</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-center">Elige un cliente con deuda pendiente para registrar su abono</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Info del cajero --}}
        <div class="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
            <x-heroicon-o-user-circle class="w-4 h-4" />
            <span>Cajero: <span class="font-semibold text-gray-500 dark:text-gray-400">{{ auth()->user()->name }}</span></span>
            <span class="text-gray-300 dark:text-gray-600">•</span>
            <span>{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</x-filament-panels::page>
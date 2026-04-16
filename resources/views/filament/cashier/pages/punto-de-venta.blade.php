<x-filament-panels::page>
    <style>
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        }
        .cart-item { animation: slideIn 0.2s ease-out; }
        .btn-cobrar:not(:disabled) { animation: pulse-green 2s infinite; }
        .cart-row:hover .cart-remove { opacity: 1; }
        .cart-remove { opacity: 0.4; transition: opacity 0.15s; }
        .cart-scroll::-webkit-scrollbar { width: 4px; }
        .cart-scroll::-webkit-scrollbar-track { background: transparent; }
        .cart-scroll::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 999px; }
        .cart-scroll::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
        .pay-btn { transition: all 0.15s ease; }
        .pay-btn:active { transform: scale(0.97); }
        .pay-section { animation: slideIn 0.2s ease-out; }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 h-[calc(100vh-10rem)]">

        {{-- ========== PANEL IZQUIERDO: Escáner + Carrito ========== --}}
        <div class="lg:col-span-8 flex flex-col gap-4 min-h-0">

            {{-- Barra de búsqueda --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 p-3 shadow-sm">
                <div class="relative flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 shrink-0">
                        <x-heroicon-o-qr-code class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <input
                        type="text"
                        wire:model="barcode"
                        wire:keydown.enter="buscarProducto"
                        class="block w-full rounded-lg border-0 py-3.5 px-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-500 text-base font-medium dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:placeholder:text-gray-500 dark:focus:ring-emerald-500 transition-all"
                        placeholder="Escanea código de barras o busca por nombre..."
                        autofocus
                    >
                    <div class="hidden sm:flex items-center gap-1.5 shrink-0 mr-1">
                        <kbd class="px-2 py-1 text-xs font-semibold text-gray-400 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">Enter</kbd>
                        <span class="text-xs text-gray-400">para buscar</span>
                    </div>
                </div>
            </div>

            {{-- Tabla del carrito --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm flex flex-col min-h-0 flex-1">

                <div class="grid grid-cols-12 gap-2 px-5 py-3 bg-gray-50/80 dark:bg-gray-800/60 border-b border-gray-100 dark:border-gray-800 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 rounded-t-xl">
                    <div class="col-span-5">Producto</div>
                    <div class="col-span-2 text-center">Precio</div>
                    <div class="col-span-2 text-center">Cantidad</div>
                    <div class="col-span-2 text-right">Subtotal</div>
                    <div class="col-span-1"></div>
                </div>

                <div class="cart-scroll flex-1 overflow-y-auto">
                    @forelse($cart as $index => $item)
                        <div class="cart-item cart-row grid grid-cols-12 gap-2 items-center px-5 py-3.5 border-b border-gray-50 dark:border-gray-800/60 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">

                            <div class="col-span-5 flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-sm font-bold shrink-0">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">{{ $item['name'] }}</p>
                                    @if(!empty($item['barcode']))
                                        <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $item['barcode'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-span-2 text-center">
                                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">${{ number_format($item['price'], 2) }}</span>
                            </div>

                            <div class="col-span-2 flex justify-center">
                                <div class="inline-flex items-center rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                                    <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="px-2.5 py-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition-colors" @if($item['quantity'] <= 1) disabled @endif>
                                        <x-heroicon-m-minus class="w-3.5 h-3.5" />
                                    </button>
                                    <input type="number" value="{{ $item['quantity'] }}" wire:change="actualizarCantidad({{ $item['id'] }}, $event.target.value)" class="w-12 text-center border-0 border-x border-gray-200 dark:border-gray-700 py-1.5 text-sm font-semibold text-gray-900 dark:text-white bg-transparent focus:ring-0" min="1">
                                    <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="px-2.5 py-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white transition-colors">
                                        <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>

                            <div class="col-span-2 text-right">
                                <span class="font-bold text-gray-900 dark:text-white text-sm">${{ number_format($item['subtotal'], 2) }}</span>
                            </div>

                            <div class="col-span-1 flex justify-center">
                                <button wire:click="eliminarDelCarrito({{ $item['id'] }})" class="cart-remove p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-16 px-4">
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-5">
                                <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">Carrito vacío</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 text-center max-w-xs">Escanea un producto con la pistola o búscalo por nombre para agregarlo</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ========== PANEL DERECHO: Resumen + Pago ========== --}}
        <div class="lg:col-span-4 flex flex-col gap-4 overflow-y-auto">

            <div class="bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm p-5 flex flex-col">

                {{-- Header resumen --}}
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center">
                        <x-heroicon-o-receipt-percent class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Resumen</h2>
                </div>

                {{-- Detalles --}}
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 dark:text-gray-400">Artículos</span>
                        <span class="font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-full text-xs">{{ collect($cart)->sum('quantity') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 dark:text-gray-400">Líneas</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ count($cart) }}</span>
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

                        <button wire:click="$set('paymentMethod', 'credit')" class="pay-btn relative flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl text-center {{ $paymentMethod === 'credit' ? 'bg-amber-50 dark:bg-amber-500/15 ring-2 ring-amber-500 text-amber-700 dark:text-amber-300 shadow-sm shadow-amber-500/10' : 'bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-200 dark:ring-gray-700 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-600 dark:hover:text-gray-300' }}">
                            @if($paymentMethod === 'credit')<div class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>@endif
                            <x-heroicon-o-document-text class="w-6 h-6" />
                            <span class="text-xs font-bold">Crédito</span>
                        </button>
                    </div>
                </div>

                {{-- ===== SECCIÓN EFECTIVO: Monto recibido + Cambio ===== --}}
                @if($paymentMethod === 'cash')
                    <div class="pay-section mt-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/20">
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-o-banknotes class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                            <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Pago en efectivo</span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-emerald-700 dark:text-emerald-300 mb-1">Monto recibido</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 dark:text-emerald-400 font-bold text-sm">$</span>
                                    <input
                                        type="number"
                                        wire:model.live="montoRecibido"
                                        class="block w-full rounded-lg border-0 py-3 pl-7 pr-4 text-lg font-bold text-emerald-900 dark:text-emerald-100 bg-white dark:bg-gray-800 ring-1 ring-inset ring-emerald-300 dark:ring-emerald-600 placeholder:text-emerald-300 dark:placeholder:text-emerald-700 focus:ring-2 focus:ring-inset focus:ring-emerald-500 transition-all"
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                    >
                                </div>
                            </div>

                            @if($montoRecibido !== '' && is_numeric($montoRecibido))
                                <div class="flex justify-between items-center px-3 py-2.5 rounded-lg {{ $cambio >= 0 && (float)$montoRecibido >= $total ? 'bg-emerald-100 dark:bg-emerald-500/20' : 'bg-red-100 dark:bg-red-500/20' }}">
                                    <span class="text-xs font-semibold {{ $cambio >= 0 && (float)$montoRecibido >= $total ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
                                        {{ (float)$montoRecibido >= $total ? 'Cambio' : 'Falta' }}
                                    </span>
                                    <span class="text-xl font-black {{ $cambio >= 0 && (float)$montoRecibido >= $total ? 'text-emerald-700 dark:text-emerald-200' : 'text-red-700 dark:text-red-200' }}">
                                        ${{ (float)$montoRecibido >= $total ? number_format($cambio, 2) : number_format($total - (float)$montoRecibido, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ===== SECCIÓN CRÉDITO: Selección de cliente ===== --}}
                @if($paymentMethod === 'credit')
                    <div class="pay-section mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/20">
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-o-document-text class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">Venta a crédito</span>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-amber-700 dark:text-amber-300 mb-1">¿A quién se le fía?</label>
                                <select
                                    wire:model.live="customerId"
                                    class="block w-full rounded-lg border-0 py-2.5 px-3 text-sm font-medium text-gray-900 dark:text-white bg-white dark:bg-gray-800 ring-1 ring-inset ring-amber-300 dark:ring-amber-600 focus:ring-2 focus:ring-inset focus:ring-amber-500 transition-all"
                                >
                                    <option value="">-- Selecciona un cliente --</option>
                                    @foreach(\App\Models\Customer::where('store_id', filament()->getTenant()->id)->orderBy('name')->get() as $cliente)
                                        <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($customerId)
                                @php
                                    $clienteSeleccionado = \App\Models\Customer::find($customerId);
                                @endphp
                                @if($clienteSeleccionado)
                                    <div class="px-3 py-3 rounded-lg bg-white dark:bg-gray-800 ring-1 ring-amber-200 dark:ring-amber-700 space-y-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-700 dark:text-amber-300 text-xs font-bold shrink-0">
                                                {{ strtoupper(substr($clienteSeleccionado->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $clienteSeleccionado->name }}</p>
                                                @if($clienteSeleccionado->phone)
                                                    <p class="text-xs text-gray-400">{{ $clienteSeleccionado->phone }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-amber-100 dark:border-amber-800">
                                            <div class="text-center px-2 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10">
                                                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Deuda actual</p>
                                                <p class="text-sm font-bold {{ $clienteSeleccionado->balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                    ${{ number_format($clienteSeleccionado->balance, 2) }}
                                                </p>
                                            </div>
                                            <div class="text-center px-2 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10">
                                                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Límite</p>
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">
                                                    {{ $clienteSeleccionado->credit_limit > 0 ? '$' . number_format($clienteSeleccionado->credit_limit, 2) : 'Sin límite' }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Alerta si se pasa del límite --}}
                                        @if($clienteSeleccionado->credit_limit > 0 && ($clienteSeleccionado->balance + $total) > $clienteSeleccionado->credit_limit)
                                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 dark:bg-red-500/10 ring-1 ring-red-200 dark:ring-red-500/20">
                                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500 shrink-0" />
                                                <p class="text-xs font-semibold text-red-600 dark:text-red-400">
                                                    Esta venta excede el límite por ${{ number_format(($clienteSeleccionado->balance + $total) - $clienteSeleccionado->credit_limit, 2) }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400/70 flex items-center gap-1.5">
                                    <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                    Selecciona un cliente para ver su información de crédito
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Total --}}
                <div class="mt-5 py-5 px-4 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 text-center shadow-lg shadow-emerald-500/20">
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-widest mb-1">Total a pagar</p>
                    <p class="text-white text-4xl font-black tracking-tight">${{ number_format($total, 2) }}</p>
                </div>

                {{-- Botones --}}
                <div class="mt-5 space-y-2.5">
                    <button
                        wire:click="procesarVenta"
                        @if(empty($cart)) disabled @endif
                        class="btn-cobrar w-full flex items-center justify-center gap-2.5 px-5 py-4 rounded-xl text-base font-bold text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-900 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:animate-none transition-all"
                    >
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        Cobrar Venta
                    </button>

                    <button
                        wire:click="vaciarCarrito"
                        @if(empty($cart)) disabled @endif
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                    >
                        <x-heroicon-o-trash class="w-4 h-4" />
                        Cancelar venta
                    </button>
                </div>
            </div>

            {{-- Atajos --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm px-5 py-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Atajos rápidos</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 text-gray-500 font-mono text-[10px]">F2</kbd>
                        <span class="text-gray-500 dark:text-gray-400">Cobrar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 text-gray-500 font-mono text-[10px]">Esc</kbd>
                        <span class="text-gray-500 dark:text-gray-400">Cancelar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 text-gray-500 font-mono text-[10px]">F1</kbd>
                        <span class="text-gray-500 dark:text-gray-400">Buscar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 text-gray-500 font-mono text-[10px]">Del</kbd>
                        <span class="text-gray-500 dark:text-gray-400">Quitar último</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') { e.preventDefault(); $wire.procesarVenta(); }
            if (e.key === 'Escape') { e.preventDefault(); $wire.vaciarCarrito(); }
            if (e.key === 'F1') { e.preventDefault(); document.querySelector('input[wire\\:model="barcode"]')?.focus(); }
        });
    </script>
    @endscript
</x-filament-panels::page>
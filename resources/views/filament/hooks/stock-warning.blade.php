@php
    $tenant = filament()->getTenant();
    if (! $tenant) return;

    $lowStockProducts = \App\Models\Product::where('store_id', $tenant->id)
        ->where('is_active', true)
        ->whereNotNull('stock_min')
        ->whereColumn('stock', '<=', 'stock_min')
        ->orderBy('stock')
        ->get(['id', 'name', 'stock', 'stock_min', 'unidad']);

    $count = $lowStockProducts->count();
    $storageKey = 'stock-warning-dismissed-' . $tenant->id;
@endphp

@if($count > 0)
    <div
        x-data="{
            open: false,
            dismissed: localStorage.getItem('{{ $storageKey }}') === '{{ now()->toDateString() }}',
            dismiss() {
                this.dismissed = true;
                localStorage.setItem('{{ $storageKey }}', '{{ now()->toDateString() }}');
            }
        }"
        x-show="!dismissed"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="border-b border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950"
    >
        {{-- Barra principal --}}
        <div class="flex items-center gap-3 px-4 py-2.5">
            <x-heroicon-s-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-500" />

            <button
                type="button"
                @click="open = !open"
                class="flex flex-1 items-center gap-2 text-left"
            >
                <span class="text-sm font-medium text-amber-800 dark:text-amber-300">
                    {{ $count }} {{ $count === 1 ? 'producto con stock bajo' : 'productos con stock bajo' }}
                </span>
                <x-heroicon-s-chevron-down
                    class="h-4 w-4 shrink-0 text-amber-500 transition-transform duration-200"
                    ::class="{ 'rotate-180': open }"
                />
            </button>

            {{-- Botón cerrar --}}
            <button
                type="button"
                @click="dismiss()"
                class="ml-auto rounded p-1 text-amber-500 hover:bg-amber-100 hover:text-amber-700 dark:hover:bg-amber-900 dark:hover:text-amber-300"
                title="Cerrar"
            >
                <x-heroicon-s-x-mark class="h-4 w-4" />
            </button>
        </div>

        {{-- Lista desplegable --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="px-4 pb-3"
        >
            <ul class="mt-1 divide-y divide-amber-100 dark:divide-amber-900">
                @foreach($lowStockProducts as $product)
                    <li class="flex items-center justify-between py-1.5 text-sm">
                        <span class="font-medium text-amber-900 dark:text-amber-200">
                            {{ $product->name }}
                        </span>
                        <span class="ml-4 shrink-0 tabular-nums text-amber-700 dark:text-amber-400">
                            {{ $product->stock }} / {{ $product->stock_min }}
                            @if($product->unidad)
                                <span class="text-xs opacity-70">{{ $product->unidad }}</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@php
    // Obtenemos el registro de forma súper segura
    $record = $getRecord();
    $items = $record ? $record->cart_items : [];

    // Si viene como texto JSON, lo convertimos a arreglo
    if (is_string($items)) {
        $items = json_decode($items, true);
    }
    $total = collect($items)->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
    $totalQty = collect($items)->sum('qty');

    $formatQty = function($qty) {
        $fractions = [
            0.125 => '⅛', 0.25 => '¼', 0.333 => '⅓',
            0.5   => '½', 0.666 => '⅔', 0.667 => '⅔',
            0.75  => '¾',
        ];
        $whole = (int) $qty;
        $decimal = round($qty - $whole, 3);
        $fraction = $fractions[$decimal] ?? null;

        if ($decimal == 0) return (string) $whole;
        if ($fraction && $whole > 0) return "{$whole} {$fraction}";
        if ($fraction) return $fraction;
        return rtrim(rtrim(number_format($qty, 3), '0'), '.');
    };
@endphp

@if(!empty($items))
    <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="bg-gray-50/75 dark:bg-white/5">
                <tr>
                    <th class="fi-ta-header-cell px-4 py-2.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 72px;">
                        Cant.
                    </th>
                    <th class="fi-ta-header-cell px-4 py-2.5 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Producto
                    </th>
                    <th class="fi-ta-header-cell px-4 py-2.5 text-end text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 120px;">
                        P. Unit.
                    </th>
                    <th class="fi-ta-header-cell px-4 py-2.5 text-end text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width: 120px;">
                        Subtotal
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($items as $item)
                    <tr class="transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        {{-- Cantidad --}}
                        <td class="px-4 py-3 text-start">
                            <span class="inline-flex items-center justify-center rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-bold text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
                                {{ $formatQty($item['qty']) }}
                            </span>
                        </td>

                        {{-- Producto --}}
                        <td class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">
                            {{ $item['name'] }}
                        </td>

                        {{-- Precio unitario --}}
                        <td class="px-4 py-3 text-end text-sm tabular-nums text-gray-500 dark:text-gray-400">
                            ${{ number_format($item['price'] ?? 0, 2) }}
                        </td>

                        {{-- Subtotal --}}
                        <td class="px-4 py-3 text-end text-sm tabular-nums font-semibold text-gray-950 dark:text-white">
                            ${{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 0), 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- Fila de total --}}
            <tfoot class="border-t border-gray-200 dark:border-white/10">
                <tr class="bg-gray-50/50 dark:bg-white/[0.03]">
                    <td class="px-4 py-3 text-start">
                        <span class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                            {{ $formatQty($totalQty) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white" colspan="1">
                        Total
                    </td>
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3 text-end">
                        <span class="text-base font-bold tabular-nums text-primary-600 dark:text-primary-400">
                            ${{ number_format($total, 2) }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    {{-- Estado vacío --}}
    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-900">
        <div class="text-gray-400 dark:text-gray-500">
            <svg class="mx-auto mb-2 h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
            <p class="text-sm font-medium">Sin productos en este pedido</p>
        </div>
    </div>
@endif
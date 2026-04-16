<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Pedido #{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* ── Reset y base para 80mm térmico ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root { --ticket-width: {{ $order->store->ticket_width ?? '80mm' }}; }

        @@page {
            size: var(--ticket-width) auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', 'Lucida Console', monospace;
            font-size: 12px;
            color: #000;
            background: #f5f5f5;
            line-height: 1.4;
        }

        .ticket {
            width: var(--ticket-width);
            max-width: var(--ticket-width);
            margin: 0 auto;
            padding: 8mm 5mm 12mm;
            background: #fff;
        }

        /* ── Encabezado ── */
        .header {
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 2px dashed #000;
            margin-bottom: 8px;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .store-sub {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .order-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 12px;
            border: 2px solid #000;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* ── Info del pedido ── */
        .info-section {
            padding: 8px 0;
            border-bottom: 1px dashed #999;
            margin-bottom: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .info-row .label {
            color: #555;
        }
        .info-row .value {
            font-weight: bold;
            text-align: right;
            max-width: 55%;
            word-break: break-word;
        }

        /* ── Tabla de productos ── */
        .products-header {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 6px 0 4px;
            border-bottom: 1px solid #000;
        }
        .col-header {
            display: flex;
            font-size: 10px;
            font-weight: bold;
            color: #555;
            padding: 4px 0;
            border-bottom: 1px dashed #ccc;
        }
        .col-header .col-qty { width: 15%; }
        .col-header .col-name { width: 45%; }
        .col-header .col-price { width: 20%; text-align: right; }
        .col-header .col-sub { width: 20%; text-align: right; }

        .product-row {
            display: flex;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 11px;
        }
        .product-row:last-child { border-bottom: none; }
        .product-row .col-qty {
            width: 15%;
            font-weight: bold;
        }
        .product-row .col-name {
            width: 45%;
            word-break: break-word;
        }
        .product-row .col-price {
            width: 20%;
            text-align: right;
            color: #555;
        }
        .product-row .col-sub {
            width: 20%;
            text-align: right;
            font-weight: bold;
        }

        /* ── Totales ── */
        .totals {
            border-top: 2px solid #000;
            margin-top: 6px;
            padding-top: 6px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
            border-top: 1px dashed #000;
            margin-top: 4px;
        }

        /* ── Notas del cliente ── */
        .notes-section {
            margin-top: 8px;
            padding: 6px 8px;
            border: 1px dashed #999;
            border-radius: 4px;
            font-size: 10px;
        }
        .notes-section .notes-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .notes-section .notes-text {
            font-style: italic;
            color: #333;
            word-break: break-word;
        }

        /* ── Estado ── */
        .status-badge {
            display: block;
            text-align: center;
            margin: 10px 0 6px;
            padding: 5px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 2px solid #000;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 2px dashed #000;
        }
        .footer p {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }
        .footer .thanks {
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin-top: 4px;
        }

        /* ── Botón NO se imprime ── */
        .no-print {
            text-align: center;
            padding: 16px;
        }
        .no-print button {
            padding: 12px 32px;
            font-size: 14px;
            font-weight: bold;
            font-family: 'Nunito', Arial, sans-serif;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 4px;
        }
        .btn-print {
            background: #111827;
            color: #fff;
        }
        .btn-print:hover { background: #1f2937; }
        .btn-close {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-close:hover { background: #e5e7eb; }

        @@media print {
            @@page { size: var(--ticket-width) auto; margin: 0; }
            body { background: #fff; }
            .ticket { width: var(--ticket-width); max-width: var(--ticket-width); padding: 2mm 3mm 6mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    @php
        $items = is_string($order->cart_items) ? json_decode($order->cart_items, true) : $order->cart_items;
        $items = is_array($items) ? $items : [];
        $totalQty = collect($items)->sum('qty');
        $statusLabels = [
            'pending'   => 'PENDIENTE',
            'ready'     => 'LISTO PARA ENTREGAR',
            'completed' => 'ENTREGADO',
            'cancelled' => 'CANCELADO',
        ];
    @endphp

    {{-- Botones de acción (no se imprimen) --}}
    <div class="no-print" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:6px 0;">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Ticket</button>
        <select id="widthSelector" onchange="cambiarAncho(this.value)"
            style="font-size:12px;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;">
            <option value="58mm">58 mm</option>
            <option value="72mm">72 mm</option>
            <option value="80mm">80 mm</option>
        </select>
        <span style="font-size:11px;color:#6b7280;">← ancho de papel</span>
        <button class="btn-close" onclick="window.close()">Cerrar</button>
    </div>

    <div class="ticket">

        {{-- ── Encabezado ── --}}
        <div class="header">
            <div class="store-name">{{ $order->store->name ?? 'Mi Tienda' }}</div>
            <div class="store-sub">Pedido en línea</div>
            <div class="order-badge">PEDIDO #{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>

        {{-- ── Info del pedido ── --}}
        <div class="info-section">
            <div class="info-row">
                <span class="label">Fecha:</span>
                <span class="value">{{ $order->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Hora:</span>
                <span class="value">{{ $order->created_at->format('H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Cliente:</span>
                <span class="value">{{ $order->customer_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tel:</span>
                <span class="value">{{ $order->customer_phone }}</span>
            </div>
        </div>

        {{-- ── Estado actual ── --}}
        <div class="status-badge">
            {{ $statusLabels[$order->status] ?? strtoupper($order->status) }}
        </div>

        {{-- ── Lista de productos ── --}}
        <div class="products-header">Productos</div>
        <div class="col-header">
            <span class="col-qty">Cant</span>
            <span class="col-name">Producto</span>
            <span class="col-price">P.Unit</span>
            <span class="col-sub">Subt.</span>
        </div>

        @foreach($items as $item)
            <div class="product-row">
                <span class="col-qty">{{ $item['qty'] }}x</span>
                <span class="col-name">{{ $item['name'] }}</span>
                <span class="col-price">${{ number_format($item['price'] ?? 0, 2) }}</span>
                <span class="col-sub">${{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 0), 2) }}</span>
            </div>
        @endforeach

        {{-- ── Totales ── --}}
        <div class="totals">
            <div class="total-row">
                <span>Artículos:</span>
                <span>{{ $totalQty }} pz</span>
            </div>
            <div class="total-final">
                <span>TOTAL:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        {{-- ── Notas del cliente ── --}}
        @if($order->notes)
            <div class="notes-section">
                <div class="notes-title">📝 Notas del cliente:</div>
                <div class="notes-text">{{ $order->notes }}</div>
            </div>
        @endif

        {{-- ── Footer ── --}}
        <div class="footer">
            <div class="thanks">¡Gracias por tu compra!</div>
            <p>{{ $order->store->name ?? '' }}</p>
            <p>{{ now()->format('d/m/Y H:i') }}</p>
        </div>

    </div>

    <script>
        var storeDefault = '{{ $order->store->ticket_width ?? '80mm' }}';

        function cambiarAncho(width) {
            localStorage.setItem('ticket_width', width);
            document.documentElement.style.setProperty('--ticket-width', width);
        }

        window.onload = function () {
            var saved = localStorage.getItem('ticket_width') || storeDefault;
            document.documentElement.style.setProperty('--ticket-width', saved);
            var sel = document.getElementById('widthSelector');
            if (sel) sel.value = saved;
        };
    </script>
</body>
</html>
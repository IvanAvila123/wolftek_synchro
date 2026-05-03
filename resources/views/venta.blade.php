<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --ticket-width: {{ $sale->store->ticket_width ?? '80mm' }}; }
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
        .store-sub { font-size: 10px; color: #555; margin-top: 2px; }
        .ticket-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 12px;
            border: 2px solid #000;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* ── Secciones de info ── */
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
        .info-row .label { color: #555; }
        .info-row .value { font-weight: bold; text-align: right; max-width: 60%; word-break: break-word; }

        /* ── Cliente opcional ── */
        .box-section {
            margin: 6px 0;
            padding: 6px 8px;
            border: 1px dashed #999;
            border-radius: 4px;
            font-size: 10px;
        }
        .box-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        /* ── Productos ── */
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
        .col-header .col-qty  { width: 12%; }
        .col-header .col-name { width: 48%; }
        .col-header .col-price { width: 20%; text-align: right; }
        .col-header .col-sub   { width: 20%; text-align: right; }
        .product-row {
            display: flex;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 11px;
            align-items: flex-start;
        }
        .product-row:last-child { border-bottom: none; }
        .product-row .col-qty   { width: 12%; font-weight: bold; }
        .product-row .col-name  { width: 48%; word-break: break-word; }
        .product-row .col-price { width: 20%; text-align: right; color: #555; }
        .product-row .col-sub   { width: 20%; text-align: right; font-weight: bold; }
        .item-barcode { font-size: 9px; color: #888; display: block; }

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
        .total-row.discount { color: #b91c1c; }
        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
            border-top: 1px dashed #000;
            margin-top: 4px;
        }

        /* ── Pagaré ── */
        .pagare-section {
            margin-top: 10px;
            padding: 8px;
            border: 1px solid #000;
            font-size: 10px;
        }
        .pagare-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .firma-linea {
            width: 85%;
            margin: 28px auto 0;
            border-bottom: 1px solid #000;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 2px dashed #000;
        }
        .footer p { font-size: 10px; color: #555; margin-bottom: 2px; }
        .footer .thanks { font-size: 13px; font-weight: bold; color: #000; margin-top: 4px; }

        /* ── Botones no se imprimen ── */
        .no-print { text-align: center; padding: 16px; }
        .no-print button {
            padding: 12px 32px;
            font-size: 14px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 4px;
        }
        .btn-print { background: #111827; color: #fff; }
        .btn-print:hover { background: #1f2937; }
        .btn-close { background: #f3f4f6; color: #374151; }
        .btn-close:hover { background: #e5e7eb; }

        @@media print {
            body { background: #fff; }
            .ticket { width: var(--ticket-width); max-width: var(--ticket-width); padding: 2mm 3mm 6mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

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
            <div class="store-name">{{ $sale->store->name }}</div>
            @if($sale->store->address)
                <div class="store-sub">{{ $sale->store->address }}</div>
            @endif
            @if($sale->store->phone)
                <div class="store-sub">Tel: {{ $sale->store->phone }}</div>
            @endif
            @if($sale->store->rfc)
                <div class="store-sub">RFC: {{ $sale->store->rfc }}</div>
            @endif
            <div class="ticket-badge">TICKET #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        {{-- ── Info del ticket ── --}}
        <div class="info-section">
            <div class="info-row">
                <span class="label">Fecha:</span>
                <span class="value">{{ $sale->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Hora:</span>
                <span class="value">{{ $sale->created_at->format('H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Cajero:</span>
                <span class="value">{{ $sale->user->name }}</span>
            </div>
        </div>

        {{-- ── Cliente (si aplica) ── --}}
        @if($sale->customer_id)
            <div class="box-section">
                <div class="box-title">Cliente</div>
                <div style="font-weight:bold;">{{ $sale->customer->name }}</div>
                @if($sale->customer->phone)
                    <div style="font-size:9px;color:#555;">Tel: {{ $sale->customer->phone }}</div>
                @endif
            </div>
        @endif

        {{-- ── Lista de productos ── --}}
        <div class="products-header">Productos</div>
        <div class="col-header">
            <span class="col-qty">Cant</span>
            <span class="col-name">Descripción</span>
            <span class="col-price">P.Unit</span>
            <span class="col-sub">Subt.</span>
        </div>

        @foreach($sale->items as $item)
            @php
                $qty = $item->quantity;
                $qtyFmt = rtrim(rtrim(number_format($qty, 3), '0'), '.');
            @endphp
            <div class="product-row">
                <span class="col-qty">{{ $qtyFmt }}</span>
                <span class="col-name">
                    {{ \Illuminate\Support\Str::limit($item->product->name, 20) }}
                    @if($item->product->barcode)
                        <span class="item-barcode">{{ $item->product->barcode }}</span>
                    @endif
                </span>
                <span class="col-price">${{ number_format($item->price, 2) }}</span>
                <span class="col-sub">${{ number_format($item->subtotal, 2) }}</span>
            </div>
        @endforeach

        {{-- ── Totales ── --}}
        <div class="totals">
            <div class="total-row">
                <span>Artículos:</span>
                <span>{{ $sale->items->sum('quantity') }}</span>
            </div>
            @if($sale->discount > 0)
                <div class="total-row discount">
                    <span>Descuento:</span>
                    <span>- ${{ number_format($sale->discount, 2) }}</span>
                </div>
            @endif
            <div class="total-final">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>

        {{-- ── Forma de pago ── --}}
        <div class="info-section" style="margin-top:6px;margin-bottom:0;">
            <div class="info-row" style="margin-bottom:0;">
                <span class="label">Forma de pago:</span>
                <span class="value">
                    {{ match($sale->payment_method) {
                        'cash'     => 'EFECTIVO',
                        'card'     => 'TARJETA',
                        'transfer' => 'TRANSFERENCIA',
                        'credit'   => 'CRÉDITO',
                        default    => strtoupper($sale->payment_method)
                    } }}
                </span>
            </div>
        </div>

        {{-- ── Pagaré (solo crédito) ── --}}
        @if($sale->payment_method === 'credit')
            <div class="pagare-section">
                <div class="pagare-title">PAGARÉ</div>
                <div style="line-height:1.5;">
                    Debo y pagaré a {{ $sale->store->name }}
                    la cantidad de <strong>${{ number_format($sale->total, 2) }} MXN</strong>.
                </div>
                <div class="firma-linea"></div>
                <div style="text-align:center;font-size:9px;color:#555;margin-top:4px;">
                    Firma del cliente: {{ $sale->customer->name ?? '' }}
                </div>
            </div>
        @endif

        {{-- ── Footer ── --}}
        <div class="footer">
            <div class="thanks">¡Gracias por su compra!</div>
            <p>Conserve su ticket para cualquier aclaración</p>
            <p>{{ $sale->store->name }}</p>
            @if($sale->store->phone)
                <p>Tel: {{ $sale->store->phone }}</p>
            @endif
        </div>

    </div>

    <script>
        var storeDefault = '{{ $sale->store->ticket_width ?? "80mm" }}';

        function applyPageSize(w) {
            var s = document.getElementById('_ps');
            if (!s) { s = document.createElement('style'); s.id = '_ps'; document.head.appendChild(s); }
            s.textContent = '@@page { size: ' + w + ' auto; margin: 0; }';
        }

        function cambiarAncho(width) {
            localStorage.setItem('ticket_width', width);
            document.documentElement.style.setProperty('--ticket-width', width);
            applyPageSize(width);
        }

        window.onload = function () {
            var saved = localStorage.getItem('ticket_width') || storeDefault;
            document.documentElement.style.setProperty('--ticket-width', saved);
            applyPageSize(saved);
            var sel = document.getElementById('widthSelector');
            if (sel) {
                sel.value = saved;
                if (sel.value !== saved) {
                    var opt = new Option(saved, saved, true, true);
                    sel.add(opt);
                }
            }
            window.print();
        };
    </script>
</body>
</html>

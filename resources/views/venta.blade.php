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
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: var(--ticket-width);
            color: #000;
            background: #fff;
        }

        @media print {
            @page { size: var(--ticket-width) auto; margin: 0; }
        }

        .ticket {
            padding: 8px 5px;
        }

        /* ---- Utilidades ---- */
        .centrado { text-align: center; }
        .derecha { text-align: right; }
        .izquierda { text-align: left; }
        .bold { font-weight: bold; }
        .text-lg { font-size: 16px; }
        .text-md { font-size: 13px; }
        .text-sm { font-size: 10px; }
        .text-xs { font-size: 9px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mb-1 { margin-bottom: 4px; }

        /* ---- Separadores ---- */
        .linea {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .linea-doble {
            border: none;
            border-top: 2px solid #000;
            margin: 8px 0;
        }
        .linea-puntos::after {
            content: '................................................';
            display: block;
            text-align: center;
            letter-spacing: 2px;
            color: #999;
            font-size: 8px;
            margin: 6px 0;
        }

        /* ---- Header tienda ---- */
        .store-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .store-info {
            font-size: 10px;
            color: #333;
            line-height: 1.5;
        }

        /* ---- Tabla productos ---- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 0;
            border-bottom: 1px solid #000;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 11px;
        }
        .items-table .item-name {
            font-size: 11px;
            font-weight: bold;
        }
        .items-table .item-detail {
            font-size: 9px;
            color: #555;
        }

        /* ---- Totales ---- */
        .totales-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totales-table td {
            padding: 2px 0;
            font-size: 12px;
        }
        .total-final td {
            padding: 6px 0 4px;
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
        }

        /* ---- Cliente credito ---- */
        .credit-box {
            border: 1px solid #000;
            padding: 5px 6px;
            margin: 6px 0;
            font-size: 10px;
        }
        .credit-box .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ---- Footer ---- */
        .footer-msg {
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }
        .footer-sub {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }

        /* ---- QR placeholder ---- */
        .qr-space {
            width: 60px;
            height: 60px;
            border: 1px dashed #ccc;
            margin: 8px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #ccc;
        }

        /* ---- Botón imprimir (no se imprime) ---- */
        .no-imprimir {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            padding: 12px;
            text-align: center;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background: #10b981;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-print:hover { background: #059669; }
        .btn-close {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 8px;
            transition: background 0.15s;
        }
        .btn-close:hover { background: #f3f4f6; }

        @media print {
            .no-imprimir { display: none !important; }
            body { width: auto; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    {{-- Barra de acciones (no se imprime) --}}
    <div class="no-imprimir" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:6px 0;">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir Ticket
        </button>
        <select id="widthSelector" onchange="cambiarAncho(this.value)"
            style="font-size:12px;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;">
            <option value="58mm">58 mm</option>
            <option value="72mm">72 mm</option>
            <option value="80mm">80 mm</option>
        </select>
        <span style="font-size:11px;color:#6b7280;">← ancho de papel (esta máquina)</span>
        <button class="btn-close" onclick="window.close()">
            ✕ Cerrar
        </button>
    </div>

    <div class="ticket">

        {{-- ======== HEADER: Tienda ======== --}}
        <div class="centrado">
            <div class="store-name">{{ $sale->store->name }}</div>
            @if($sale->store->address)
                <div class="store-info">{{ $sale->store->address }}</div>
            @endif
            @if($sale->store->phone)
                <div class="store-info">Tel: {{ $sale->store->phone }}</div>
            @endif
            @if($sale->store->rfc)
                <div class="store-info">RFC: {{ $sale->store->rfc }}</div>
            @endif
        </div>

        <hr class="linea-doble">

        {{-- ======== INFO DEL TICKET ======== --}}
        <table style="width:100%; font-size:10px;">
            <tr>
                <td class="izquierda bold">Ticket:</td>
                <td class="derecha">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Fecha:</td>
                <td class="derecha">{{ $sale->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Hora:</td>
                <td class="derecha">{{ $sale->created_at->format('H:i:s') }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Cajero:</td>
                <td class="derecha">{{ $sale->user->name }}</td>
            </tr>
        </table>

        {{-- ======== CLIENTE (si aplica) ======== --}}
        @if($sale->customer_id)
            <div class="credit-box mt-1">
                <div class="label bold">Cliente:</div>
                <div>{{ $sale->customer->name }}</div>
                @if($sale->customer->phone)
                    <div class="text-xs" style="color:#555;">Tel: {{ $sale->customer->phone }}</div>
                @endif
            </div>
        @endif

        <hr class="linea">

        {{-- ======== PRODUCTOS ======== --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th class="izquierda" style="width:10%;">Cant</th>
                    <th class="izquierda" style="width:50%;">Descripción</th>
                    <th class="derecha" style="width:20%;">P.Unit</th>
                    <th class="derecha" style="width:20%;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ number_format($item->quantity, 0) }}</td>
                        <td>
                            <div class="item-name">{{ \Illuminate\Support\Str::limit($item->product->name, 18) }}</div>
                            @if($item->product->barcode)
                                <div class="item-detail">{{ $item->product->barcode }}</div>
                            @endif
                        </td>
                        <td class="derecha">${{ number_format($item->price, 2) }}</td>
                        <td class="derecha bold">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="linea">

        {{-- ======== TOTALES ======== --}}
        <table class="totales-table">
            <tr>
                <td class="izquierda">Artículos:</td>
                <td class="derecha">{{ $sale->items->sum('quantity') }}</td>
            </tr>
            @if($sale->discount > 0)
                <tr>
                    <td class="izquierda">Descuento:</td>
                    <td class="derecha">- ${{ number_format($sale->discount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-final">
                <td class="izquierda">TOTAL:</td>
                <td class="derecha">${{ number_format($sale->total, 2) }}</td>
            </tr>
        </table>

        {{-- ======== MÉTODO DE PAGO ======== --}}
        <div class="mt-2" style="font-size:11px;">
            <table style="width:100%;">
                <tr>
                    <td class="izquierda bold">Pago:</td>
                    <td class="derecha">
                        {{ match($sale->payment_method) {
                            'cash' => 'EFECTIVO',
                            'card' => 'TARJETA',
                            'transfer' => 'TRANSFERENCIA',
                            'credit' => 'CRÉDITO',
                            default => strtoupper($sale->payment_method)
                        } }}
                    </td>
                </tr>
                @if($sale->payment_method === 'credit')
                    <tr>
                        <td class="izquierda bold" style="color:#000;">Tipo:</td>
                        <td class="derecha" style="color:#000;">VENTA A CRÉDITO</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- ======== FIRMA (solo crédito) ======== --}}
        @if($sale->payment_method === 'credit')
            <div class="mt-2">
                <hr class="linea">
                <div class="centrado text-xs bold mb-1">PAGARÉ</div>
                <div class="text-xs" style="line-height:1.4;">
                    Debo y pagaré a {{ $sale->store->name }}
                    la cantidad de ${{ number_format($sale->total, 2) }} MXN.
                </div>
                <div style="margin-top:25px; border-bottom:1px solid #000; width:80%; margin-left:auto; margin-right:auto;"></div>
                <div class="centrado text-xs mt-1" style="color:#555;">
                    Firma del cliente: {{ $sale->customer->name ?? '' }}
                </div>
            </div>
        @endif

        <hr class="linea-doble">

        {{-- ======== FOOTER ======== --}}
        <div class="centrado">
            <div class="footer-msg">¡Gracias por su compra!</div>
            <div class="footer-sub">Conserve su ticket para cualquier aclaración</div>
            <div class="footer-sub mt-1">{{ $sale->store->name }}</div>
            @if($sale->store->phone)
                <div class="footer-sub">Tel: {{ $sale->store->phone }}</div>
            @endif
        </div>

        <div class="linea-puntos"></div>

    </div>

    <script>
        var storeDefault = '{{ $sale->store->ticket_width ?? '80mm' }}';

        function cambiarAncho(width) {
            localStorage.setItem('ticket_width', width);
            document.documentElement.style.setProperty('--ticket-width', width);
        }

        window.onload = function () {
            // Leer override local; si no hay, usar el default de la tienda
            var saved = localStorage.getItem('ticket_width') || storeDefault;
            document.documentElement.style.setProperty('--ticket-width', saved);

            var sel = document.getElementById('widthSelector');
            if (sel) {
                sel.value = saved;
                // Si el guardado no coincide con ninguna opción, añadirla
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
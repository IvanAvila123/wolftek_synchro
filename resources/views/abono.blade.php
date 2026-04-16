<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abono #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root { --ticket-width: {{ $payment->store->ticket_width ?? '80mm' }}; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: var(--ticket-width);
            color: #000;
            background: #fff;
        }

        .ticket { padding: 8px 5px; }

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

        /* ---- Saldo box ---- */
        .saldo-box {
            border: 1px solid #000;
            padding: 5px 6px;
            margin: 6px 0;
            font-size: 10px;
        }
        .saldo-box .label {
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

        /* ---- Botones (no se imprimen) ---- */
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
            @@page { size: var(--ticket-width) auto; margin: 0; }
            .no-imprimir { display: none !important; }
            body { width: var(--ticket-width); margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    {{-- Barra de acciones (no se imprime) --}}
    <div class="no-imprimir" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:6px 0;">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir Comprobante
        </button>
        <select id="widthSelector" onchange="cambiarAncho(this.value)"
            style="font-size:12px;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;">
            <option value="58mm">58 mm</option>
            <option value="72mm">72 mm</option>
            <option value="80mm">80 mm</option>
        </select>
        <span style="font-size:11px;color:#6b7280;">← ancho de papel</span>
        <button class="btn-close" onclick="window.close()">
            ✕ Cerrar
        </button>
    </div>

    <div class="ticket">

        {{-- ======== HEADER: Tienda ======== --}}
        <div class="centrado">
            <div class="store-name">{{ $payment->store->name }}</div>
            @if($payment->store->address)
                <div class="store-info">{{ $payment->store->address }}</div>
            @endif
            @if($payment->store->phone)
                <div class="store-info">Tel: {{ $payment->store->phone }}</div>
            @endif
            @if($payment->store->rfc)
                <div class="store-info">RFC: {{ $payment->store->rfc }}</div>
            @endif
        </div>

        <hr class="linea-doble">

        {{-- Título del comprobante --}}
        <div class="centrado bold" style="font-size: 13px; letter-spacing: 1px; margin-bottom: 4px;">
            COMPROBANTE DE ABONO
        </div>

        <hr class="linea">

        {{-- ======== INFO DEL COMPROBANTE ======== --}}
        <table style="width:100%; font-size:10px;">
            <tr>
                <td class="izquierda bold">Folio:</td>
                <td class="derecha">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Fecha:</td>
                <td class="derecha">{{ $payment->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Hora:</td>
                <td class="derecha">{{ $payment->created_at->format('H:i:s') }}</td>
            </tr>
            @if(isset($payment->user) && $payment->user)
            <tr>
                <td class="izquierda bold">Cajero:</td>
                <td class="derecha">{{ $payment->user->name }}</td>
            </tr>
            @endif
        </table>

        <hr class="linea">

        {{-- ======== CLIENTE ======== --}}
        <div class="saldo-box">
            <div class="label bold">Cliente:</div>
            <div class="bold" style="font-size: 12px;">{{ $payment->customer->name }}</div>
            @if($payment->customer->phone)
                <div class="text-xs" style="color:#555;">Tel: {{ $payment->customer->phone }}</div>
            @endif
        </div>

        <hr class="linea">

        {{-- ======== DETALLE DEL ABONO ======== --}}
        <table class="totales-table">
            <tr>
                <td class="izquierda">Método de pago:</td>
                <td class="derecha bold">
                    {{ match($payment->payment_method) {
                        'cash'     => 'EFECTIVO',
                        'card'     => 'TARJETA',
                        'transfer' => 'TRANSFERENCIA',
                        default    => strtoupper($payment->payment_method)
                    } }}
                </td>
            </tr>
            <tr class="total-final">
                <td class="izquierda">MONTO ABONADO:</td>
                <td class="derecha">${{ number_format($payment->amount, 2) }}</td>
            </tr>
        </table>

        <hr class="linea">

        {{-- ======== SALDO RESTANTE ======== --}}
        <table style="width:100%; font-size:11px;">
            <tr>
                <td class="izquierda bold">Saldo anterior:</td>
                <td class="derecha">${{ number_format($payment->customer->balance + $payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="izquierda">Este abono:</td>
                <td class="derecha">- ${{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="izquierda bold">Saldo restante:</td>
                <td class="derecha bold" style="font-size:14px;">
                    ${{ number_format($payment->customer->balance, 2) }}
                </td>
            </tr>
        </table>

        @if($payment->customer->balance <= 0)
            <div class="centrado mt-2 bold" style="font-size: 11px; border: 1px solid #000; padding: 4px;">
                ✓ DEUDA LIQUIDADA
            </div>
        @endif

        <hr class="linea-doble">

        {{-- ======== FOOTER ======== --}}
        <div class="centrado">
            <div class="footer-msg">¡Gracias por su pago!</div>
            <div class="footer-sub">Conserve este comprobante para cualquier aclaración</div>
            <div class="footer-sub mt-1">{{ $payment->store->name }}</div>
            @if($payment->store->phone)
                <div class="footer-sub">Tel: {{ $payment->store->phone }}</div>
            @endif
        </div>

        <div class="linea-puntos"></div>

    </div>
    

    <script>
        var storeDefault = '{{ $payment->store->ticket_width ?? '80mm' }}';

        function cambiarAncho(width) {
            localStorage.setItem('ticket_width', width);
            document.documentElement.style.setProperty('--ticket-width', width);
        }

        window.onload = function () {
            var saved = localStorage.getItem('ticket_width') || storeDefault;
            document.documentElement.style.setProperty('--ticket-width', saved);
            var sel = document.getElementById('widthSelector');
            if (sel) sel.value = saved;
            window.print();
        };
    </script>
</body>
</html>
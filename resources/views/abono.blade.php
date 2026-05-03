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
        .store-name { font-size: 18px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .store-sub  { font-size: 10px; color: #555; margin-top: 2px; }
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

        /* ── Caja de sección ── */
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
        .total-row.abono { color: #166534; }
        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
            border-top: 1px dashed #000;
            margin-top: 4px;
        }

        /* ── Badge liquidado ── */
        .badge-liquidado {
            display: block;
            text-align: center;
            margin: 8px 0 4px;
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
        .footer p       { font-size: 10px; color: #555; margin-bottom: 2px; }
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
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Comprobante</button>
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
            <div class="store-name">{{ $payment->store->name }}</div>
            @if($payment->store->address)
                <div class="store-sub">{{ $payment->store->address }}</div>
            @endif
            @if($payment->store->phone)
                <div class="store-sub">Tel: {{ $payment->store->phone }}</div>
            @endif
            @if($payment->store->rfc)
                <div class="store-sub">RFC: {{ $payment->store->rfc }}</div>
            @endif
            <div class="ticket-badge">COMPROBANTE DE ABONO</div>
        </div>

        {{-- ── Info del comprobante ── --}}
        <div class="info-section">
            <div class="info-row">
                <span class="label">Folio:</span>
                <span class="value">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha:</span>
                <span class="value">{{ $payment->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Hora:</span>
                <span class="value">{{ $payment->created_at->format('H:i:s') }}</span>
            </div>
            @if(isset($payment->user) && $payment->user)
                <div class="info-row">
                    <span class="label">Cajero:</span>
                    <span class="value">{{ $payment->user->name }}</span>
                </div>
            @endif
        </div>

        {{-- ── Cliente ── --}}
        <div class="box-section">
            <div class="box-title">Cliente</div>
            <div style="font-weight:bold;">{{ $payment->customer->name }}</div>
            @if($payment->customer->phone)
                <div style="font-size:9px;color:#555;">Tel: {{ $payment->customer->phone }}</div>
            @endif
        </div>

        {{-- ── Detalle del abono ── --}}
        <div class="totals">
            <div class="total-row">
                <span>Forma de pago:</span>
                <span style="font-weight:bold;">
                    {{ match($payment->payment_method) {
                        'cash'     => 'EFECTIVO',
                        'card'     => 'TARJETA',
                        'transfer' => 'TRANSFERENCIA',
                        default    => strtoupper($payment->payment_method)
                    } }}
                </span>
            </div>
            <div class="total-final">
                <span>MONTO ABONADO:</span>
                <span>${{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        {{-- ── Saldo ── --}}
        <div class="info-section" style="margin-top:8px;">
            <div class="info-row">
                <span class="label">Saldo anterior:</span>
                <span class="value">${{ number_format($payment->customer->balance + $payment->amount, 2) }}</span>
            </div>
            <div class="info-row abono" style="color:#166534;">
                <span class="label" style="color:#166534;">Este abono:</span>
                <span class="value" style="color:#166534;">- ${{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="info-row" style="font-size:13px;">
                <span class="label" style="color:#000;font-weight:bold;">Saldo restante:</span>
                <span class="value">${{ number_format($payment->customer->balance, 2) }}</span>
            </div>
        </div>

        @if($payment->customer->balance <= 0)
            <div class="badge-liquidado">✓ DEUDA LIQUIDADA</div>
        @endif

        {{-- ── Footer ── --}}
        <div class="footer">
            <div class="thanks">¡Gracias por su pago!</div>
            <p>Conserve este comprobante para cualquier aclaración</p>
            <p>{{ $payment->store->name }}</p>
            @if($payment->store->phone)
                <p>Tel: {{ $payment->store->phone }}</p>
            @endif
        </div>

    </div>

    <script>
        var storeDefault = '{{ $payment->store->ticket_width ?? "80mm" }}';

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

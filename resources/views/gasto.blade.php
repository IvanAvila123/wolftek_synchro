<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vale de Salida #{{ str_pad($expense->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --ticket-width: {{ $expense->store->ticket_width ?? '80mm' }}; }
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

        /* ── Caja de concepto ── */
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

        /* ── Total ── */
        .totals {
            border-top: 2px solid #000;
            margin-top: 6px;
            padding-top: 6px;
        }
        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
        }

        /* ── Firmas ── */
        .firma-block { margin-top: 32px; }
        .firma-linea-solida   { width: 85%; margin: 0 auto; border-bottom: 1px solid #000; }
        .firma-linea-punteada { width: 85%; margin: 0 auto; border-bottom: 1px dashed #000; }
        .firma-label { text-align: center; font-size: 9px; color: #555; margin-top: 4px; }

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
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir Vale de Salida</button>
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
            <div class="store-name">{{ $expense->store->name }}</div>
            @if($expense->store->address)
                <div class="store-sub">{{ $expense->store->address }}</div>
            @endif
            @if($expense->store->phone)
                <div class="store-sub">Tel: {{ $expense->store->phone }}</div>
            @endif
            @if($expense->store->rfc)
                <div class="store-sub">RFC: {{ $expense->store->rfc }}</div>
            @endif
            <div class="ticket-badge">VALE DE SALIDA DE EFECTIVO</div>
        </div>

        {{-- ── Info del documento ── --}}
        <div class="info-section">
            <div class="info-row">
                <span class="label">Folio:</span>
                <span class="value">#{{ str_pad($expense->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha:</span>
                <span class="value">{{ $expense->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Hora:</span>
                <span class="value">{{ $expense->created_at->format('H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Autorizó:</span>
                <span class="value">{{ $expense->user->name }}</span>
            </div>
        </div>

        {{-- ── Concepto ── --}}
        <div class="box-section">
            <div class="box-title">Concepto / Motivo</div>
            <div style="font-size:11px;line-height:1.5;">{{ $expense->concept }}</div>
        </div>

        {{-- ── Monto ── --}}
        <div class="totals">
            <div class="total-final">
                <span>MONTO RETIRADO:</span>
                <span>${{ number_format($expense->amount, 2) }}</span>
            </div>
        </div>

        {{-- ── Firmas ── --}}
        <div class="firma-block">
            <div class="firma-linea-solida"></div>
            <div class="firma-label">Nombre y firma de quien recibe el dinero</div>
        </div>

        <div class="firma-block">
            <div class="firma-linea-punteada"></div>
            <div class="firma-label">Firma del cajero — {{ $expense->user->name }}</div>
        </div>

        {{-- ── Footer ── --}}
        <div class="footer">
            <div class="thanks">Este vale ampara la salida de efectivo</div>
            <p>Consérvelo como comprobante de la operación</p>
            <p>{{ $expense->store->name }}</p>
            @if($expense->store->phone)
                <p>Tel: {{ $expense->store->phone }}</p>
            @endif
        </div>

    </div>

    <script>
        var storeDefault = '{{ $expense->store->ticket_width ?? "80mm" }}';

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

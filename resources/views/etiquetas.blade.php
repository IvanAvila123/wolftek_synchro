<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión de Etiquetas — {{ $products->count() }} productos</title>
    <style>
        /* ============================================================
           PANTALLA — Vista previa
        ============================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #f1f5f9;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            min-height: 100vh;
        }

        /* ---- Barra superior ---- */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #111827;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toolbar-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .badge {
            background: #374151;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 12px;
            color: #9ca3af;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            background: #10b981;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-print:hover { background: #059669; }

        /* ---- Grid de vista previa ---- */
        .preview-area {
            padding: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-start;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ============================================================
           ETIQUETA — Diseño físico (50mm × 25mm)
           Se usa tanto en pantalla como al imprimir
        ============================================================ */
        .etiqueta {
            width: 50mm;
            height: 25mm;
            background: #fff;
            color: #000;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2mm 2.5mm 1.5mm;
            /* Borde visual en pantalla */
            border: 0.5px solid #d1d5db;
            border-radius: 2px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        /* ---- Encabezado: tienda + nombre producto ---- */
        .etiqueta-header {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .etiqueta-tienda {
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1;
            margin-bottom: 1px;
        }
        .etiqueta-nombre {
            font-size: 8.5px;
            font-weight: 800;
            line-height: 1.2;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ---- Centro: precio ---- */
        .etiqueta-precio {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #111827;
            line-height: 1;
        }
        .etiqueta-precio .signo {
            font-size: 8px;
            font-weight: 700;
            vertical-align: top;
            margin-top: 1px;
            display: inline-block;
        }

        /* ---- Pie: código de barras ---- */
        .etiqueta-footer {
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .codigo-barras {
            height: 8mm;
            width: 100%;
            max-width: 46mm;
        }

        /* ============================================================
           IMPRESIÓN
        ============================================================ */
        @media print {
            .toolbar, .preview-area { display: none !important; }

            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .print-area {
                display: flex;
                flex-wrap: wrap;
                /* Ajusta estos valores al tamaño de tu hoja de etiquetas */
                gap: 2mm;
                padding: 5mm;
            }

            .etiqueta {
                box-shadow: none;
                border: 0.3px solid #ccc; /* útil para recorte, quitar si no se desea */
                border-radius: 0;
                /* Evita que la etiqueta se parta entre páginas */
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- ======== BARRA DE HERRAMIENTAS (solo pantalla) ======== --}}
    <div class="toolbar no-imprimir">
        <div class="toolbar-info">
            <span class="toolbar-title">🏷️ Vista previa de etiquetas</span>
            <span class="badge">{{ $products->count() }} etiqueta{{ $products->count() !== 1 ? 's' : '' }}</span>
        </div>
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir etiquetas
        </button>
    </div>

    {{-- ======== VISTA PREVIA (solo pantalla) ======== --}}
    <div class="preview-area no-imprimir">
        @foreach($products as $product)
            <div class="etiqueta">
                <div class="etiqueta-header">
                    <div class="etiqueta-tienda">{{ $product->store->name ?? config('app.name') }}</div>
                    <div class="etiqueta-nombre">{{ $product->name }}</div>
                </div>

                <div class="etiqueta-precio">
                    <span class="signo">$</span>{{ number_format($product->price_sell, 2) }}
                </div>

                <div class="etiqueta-footer">
                    <svg class="codigo-barras"
                         jsbarcode-format="CODE128"
                         jsbarcode-value="{{ $product->barcode }}"
                         jsbarcode-textmargin="0"
                         jsbarcode-fontoptions="bold"
                         jsbarcode-height="20"
                         jsbarcode-width="1"
                         jsbarcode-displayvalue="true"
                         jsbarcode-fontsize="7"
                         jsbarcode-margin="0">
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ======== ÁREA DE IMPRESIÓN (solo al imprimir) ======== --}}
    <div class="print-area">
        @foreach($products as $product)
            <div class="etiqueta">
                <div class="etiqueta-header">
                    <div class="etiqueta-tienda">{{ $product->store->name ?? config('app.name') }}</div>
                    <div class="etiqueta-nombre">{{ $product->name }}</div>
                </div>

                <div class="etiqueta-precio">
                    <span class="signo">$</span>{{ number_format($product->price_sell, 2) }}
                </div>

                <div class="etiqueta-footer">
                    <svg class="codigo-barras"
                         jsbarcode-format="CODE128"
                         jsbarcode-value="{{ $product->barcode }}"
                         jsbarcode-textmargin="0"
                         jsbarcode-fontoptions="bold"
                         jsbarcode-height="20"
                         jsbarcode-width="1"
                         jsbarcode-displayvalue="true"
                         jsbarcode-fontsize="7"
                         jsbarcode-margin="0">
                    </svg>
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode(".codigo-barras").init();
    </script>
</body>
</html>
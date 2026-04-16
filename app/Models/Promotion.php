<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'store_id', 'product_batch_id', 'product_id',
        'nombre', 'tipo', 'valor',
        'cantidad_paga', 'cantidad_lleva',
        'activa', 'auto_activar_dias',
        'starts_at', 'ends_at',
    ];

    protected $casts = [
        'activa'      => 'boolean',
        'starts_at'   => 'date',
        'ends_at'     => 'date',
        'valor'       => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Determina si esta promoción está vigente en este momento.
     * Tiene en cuenta: activa manual, auto_activar_dias y rango de fechas.
     */
    public function estaVigente(): bool
    {
        $today = now()->startOfDay();

        // Verificar rango de fechas
        if ($this->starts_at && $today->lt($this->starts_at)) return false;
        if ($this->ends_at   && $today->gt($this->ends_at))   return false;

        // Activación manual
        if ($this->activa) return true;

        // Auto-activación por proximidad de caducidad
        if ($this->auto_activar_dias && $this->batch?->expiry_date) {
            $diasRestantes = $today->diffInDays($this->batch->expiry_date, false);
            return $diasRestantes >= 0 && $diasRestantes <= $this->auto_activar_dias;
        }

        return false;
    }

    /**
     * Calcula el subtotal aplicando la promoción.
     *
     * Devuelve:
     *   price_unit  — precio unitario efectivo a mostrar
     *   subtotal    — total a cobrar por esa cantidad
     *   ahorro      — cuánto se ahorra el cliente
     *   label       — texto corto para mostrar en ticket/POS
     */
    public function calcularSubtotal(float $quantity, float $precioOriginal): array
    {
        $subtotalSinPromo = $quantity * $precioOriginal;

        switch ($this->tipo) {
            case 'porcentaje':
                $factor     = 1 - ($this->valor / 100);
                $priceUnit  = round($precioOriginal * $factor, 2);
                $subtotal   = round($quantity * $priceUnit, 2);
                $label      = "-{$this->valor}% ({$this->nombre})";
                break;

            case 'precio_fijo':
                $priceUnit  = (float) $this->valor;
                $subtotal   = round($quantity * $priceUnit, 2);
                $label      = "\${$priceUnit} c/u ({$this->nombre})";
                break;

            case 'nxm':
                // paga X lleva Y  → por cada bloque de $lleva unidades, cobras $paga
                $paga  = (int) $this->cantidad_paga;
                $lleva = (int) $this->cantidad_lleva;

                if ($lleva <= 0 || $paga <= 0) {
                    // Datos inválidos, no aplica descuento
                    return $this->sinPromo($quantity, $precioOriginal);
                }

                $bloques       = (int) floor($quantity / $lleva);
                $resto         = fmod($quantity, $lleva);
                $subtotal      = round(($bloques * $paga + $resto) * $precioOriginal, 2);
                $priceUnit     = $quantity > 0 ? round($subtotal / $quantity, 4) : $precioOriginal;
                $label         = "{$lleva}x{$paga} ({$this->nombre})";
                break;

            default:
                return $this->sinPromo($quantity, $precioOriginal);
        }

        return [
            'price_unit'   => $priceUnit,
            'subtotal'     => $subtotal,
            'ahorro'       => round($subtotalSinPromo - $subtotal, 2),
            'label'        => $label,
            'promo_id'     => $this->id,
            'tipo'         => $this->tipo,
            'cantidad_paga'  => $this->cantidad_paga,
            'cantidad_lleva' => $this->cantidad_lleva,
        ];
    }

    private function sinPromo(float $quantity, float $precio): array
    {
        return [
            'price_unit'     => $precio,
            'subtotal'       => round($quantity * $precio, 2),
            'ahorro'         => 0,
            'label'          => null,
            'promo_id'       => null,
            'tipo'           => null,
            'cantidad_paga'  => null,
            'cantidad_lleva' => null,
        ];
    }

    /**
     * Etiqueta legible del tipo de promoción.
     */
    public static function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'porcentaje'  => '% Descuento',
            'precio_fijo' => 'Precio Especial',
            'nxm'         => 'NxM (lleva más, paga menos)',
            default       => $tipo,
        };
    }
}

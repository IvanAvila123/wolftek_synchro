<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    protected $fillable = [
        'store_id', 'category_id', 'name', 'barcode', 'plu',
        'description', 'price_buy', 'price_sell',
        'stock', 'stock_min', 'unidad', 'has_scale', 'is_active'
    ];

    protected $casts = [
        'price_buy' => 'decimal:2',
        'price_sell' => 'decimal:2',
        'has_scale' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_products');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Devuelve la promoción vigente del lote más próximo a caducar (FEFO).
     * Es el método que usa el POS al escanear un producto.
     */
    public function promocionActivaFefo(): ?Promotion
    {
        return $this->batches()
            ->with('promotions')
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(fn ($batch) => $batch->promocionActiva())
            ->filter()
            ->first();
    }

    /**
     * Verificar si el stock está bajo
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_min;
    }

    /**
     * Margen de ganancia
     */
    public function getMarginAttribute(): ?float
    {
        if (!$this->price_buy || $this->price_buy == 0) {
            return null;
        }
        return round((($this->price_sell - $this->price_buy) / $this->price_buy) * 100, 2);
    }
}
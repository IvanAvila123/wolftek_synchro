<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id', 'quantity', 'expiry_date'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Devuelve la promoción vigente de este lote (si existe).
     */
    public function promocionActiva(): ?Promotion
    {
        return $this->promotions
            ->first(fn (Promotion $p) => $p->estaVigente());
    }
}
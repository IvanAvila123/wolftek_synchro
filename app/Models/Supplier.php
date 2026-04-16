<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'store_id', 'name', 'company', 'phone',
        'email', 'credit_limit', 'balance'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'supplier_products');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
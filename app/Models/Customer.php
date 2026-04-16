<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'store_id', 'name', 'phone', 'credit_limit', 'balance'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditSales()
    {
        return $this->hasMany(CreditSale::class);
    }
}
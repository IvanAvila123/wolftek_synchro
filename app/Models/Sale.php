<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'store_id', 'cash_shift_id', 'user_id', 'customer_id',
        'total', 'discount', 'payment_method', 'status'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creditSale()
    {
        return $this->hasOne(CreditSale::class);
    }
}
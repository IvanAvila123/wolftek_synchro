<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = [
        'store_id',
        'cash_shift_id',
        'customer_id',
        'amount',
        'payment_method',
        'user_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Le decimos que un abono "pertenece a" una Tienda
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Le decimos que un abono "pertenece a" un Turno de Caja
    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

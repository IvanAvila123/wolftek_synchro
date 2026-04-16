<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditSale extends Model
{
    protected $fillable = [
        'customer_id', 'sale_id', 'amount',
        'paid_amount', 'status', 'due_date'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
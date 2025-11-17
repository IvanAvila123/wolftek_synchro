<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id',
        'store_id',
        'customer_name',
        'costumer_phone',
        'status',
        'total',
        'payment_method',
        'payment_status',
    ];
}

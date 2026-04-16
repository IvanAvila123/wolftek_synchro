<?php

namespace App\Models;

use App\Observers\OnlineOrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(OnlineOrderObserver::class)]
class OnlineOrder extends Model
{
    protected $fillable = [
        'store_id',
        'customer_name',
        'customer_phone',
        'notes',
        'total',
        'status',
        'cart_items',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

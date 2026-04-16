<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    // 👇 ESTE ES EL PASE VIP QUE NOS FALTABA 👇
    protected $fillable = [
        'store_id',
        'plan_id',
        'estatus',
        'payment_method',
        'conekta_subscription_id',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
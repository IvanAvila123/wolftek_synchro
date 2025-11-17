<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartShelf extends Model
{

    protected $fillable = [
        'id',
        'store_id',
        'product_id',
        'hardware_id',
        'status',
        'current_weight',
        'last_reported_at',
    ];

    protected $casts = [
        'last_reported_at' => 'datetime',
    ];

    public function store(): BelongsTo
{
    return $this->belongsTo(Store::class);
}

public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

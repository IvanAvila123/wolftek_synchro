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

    public function store(): BelongsTo
{
    return $this->belongsTo(Store::class);
}
}

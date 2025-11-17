<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'store_id',
        'name',
        'description',
        'price',
        'image_url',
        'sku',
        'stock_count',
        'is_visible_on_quickorder',
        'unit_weight_grams',
        'low_stock_threshold',
    ];
}

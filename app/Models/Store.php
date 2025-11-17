<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'id',
        'name',
        'logo_url',
        'whatsapp_number',
        'slug',
        'mercado_pago_key',
        'clabe_interbancaria',

    ];


    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function smartShelves(): HasMany
    {
        return $this->hasMany(SmartShelf::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


}

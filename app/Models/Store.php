<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    
}

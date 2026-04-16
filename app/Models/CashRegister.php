<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'store_id', 'name'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function shifts()
    {
        return $this->hasMany(CashShift::class);
    }
}
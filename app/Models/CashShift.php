<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    protected $fillable = [
        'cash_register_id', 'user_id', 'opening_amount',
        'closing_amount', 'status', 'opened_at', 'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
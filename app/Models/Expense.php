<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'store_id',
        'cash_shift_id',
        'user_id',
        'concept',
        'amount',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
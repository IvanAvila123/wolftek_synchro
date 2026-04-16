<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'price', 'max_users', 'max_branches', 'features'
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'rfc',
        'curp',
        'fiscal_document',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Helpers para acceder al rol del usuario
    public function getRoleAttribute(): ?string
    {
        return $this->user?->roles->first()?->name;
    }

    public function hasRole(string $role): bool
    {
        return $this->user?->hasRole($role) ?? false;
    }

    // Nombre del empleado directo
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Sin nombre';
    }

    // Email del empleado directo
    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }
}
<?php

namespace App\Models;

use Andreia\FilamentUiSwitcher\Models\Traits\HasUiPreferences;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasRoles, HasUiPreferences;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ui_preferences' => 'array',
        ];
    }

    /**
     * Control de acceso a paneles.
     * Lee el campo `panel` del rol asignado al usuario para decidir el acceso.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'superadmin') {
            return $this->hasDirectRole(['super_admin']);
        }

        // super_admin nunca entra a admin ni cashier por esta vía
        if ($this->hasDirectRole(['super_admin'])) {
            return false;
        }

        $rolePanel = $this->getRolePanel();

        return $rolePanel === $panel->getId();
    }

    /**
     * Devuelve el valor del campo `panel` del rol asignado al usuario.
     * Ej: 'admin', 'cashier', o null si el rol no tiene acceso a ningún panel.
     */
    public function getRolePanel(): ?string
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', get_class($this))
            ->value('roles.panel');
    }

    /**
     * Verificar rol sin depender del team context de Spatie.
     * Query directa a model_has_roles.
     */
    public function hasDirectRole(array $roles): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', get_class($this))
            ->whereIn('roles.name', $roles)
            ->exists();
    }

    /**
     * Obtener el nombre del rol sin filtro de team.
     * Útil para mostrar en tablas e infolists.
     */
    public function getDirectRoleName(): ?string
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', get_class($this))
            ->value('roles.name');
    }

    // Relaciones
    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // Requerido por HasTenants
    public function getTenants(Panel $panel): Collection
    {
        // Owner: su tienda directa
        if ($this->store) {
            return Collection::make([$this->store]);
        }

        // Empleado: la tienda donde trabaja
        if ($this->employee) {
            return Collection::make([$this->employee->store])->filter();
        }

        return Collection::make();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        // Owner
        if ($this->store?->id === $tenant->id) {
            return true;
        }

        // Empleado
        if ($this->employee?->store_id === $tenant->id) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Store extends Model implements HasName
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'name',
        'business_type',
        'rfc',
        'address',
        'phone',
        'logo',
        'estatus',
        'trial_ends_at',
        'whatsapp_number',
        'catalog_description',
        'valid_until',
        'is_active',
        'ticket_width',
    ];

    /**
     * URL pública del logo (devuelve null si no hay logo guardado).
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? asset('storage/' . $this->logo) : null,
        );
    }

    /**
     * Tipos de negocio disponibles con su etiqueta y categorías predeterminadas.
     */
    public static function businessTypes(): array
    {
        return [
            'abarrotes'     => 'Abarrotes / Miscelánea',
            'dulceria'      => 'Dulcería / Confitería',
            'refaccionaria' => 'Refaccionaria / Autopartes',
            'ferreteria'    => 'Ferretería / Tlapalería',
            'farmacia'      => 'Farmacia / Botica',
            'papeleria'     => 'Papelería / Librería',
            'carniceria'    => 'Carnicería / Pollería',
            'panaderia'     => 'Panadería / Pastelería',
            'ropa'          => 'Ropa / Calzado / Accesorios',
            'electronica'   => 'Electrónica / Computación',
            'otro'          => 'Otro tipo de negocio',
        ];
    }

    /**
     * Categorías predeterminadas por tipo de negocio.
     */
    public static function defaultCategoriesFor(string $businessType): array
    {
        return match ($businessType) {
            'abarrotes' => [
                'Lácteos', 'Bebidas', 'Frituras y Botanas', 'Conservas y Enlatados',
                'Carnes y Embutidos', 'Limpieza', 'Higiene Personal', 'Cereales y Granos', 'Dulces',
            ],
            'dulceria' => [
                'Chocolates', 'Gomitas y Gelatinas', 'Paletas y Helados', 'Chiclosos y Caramelos',
                'Mazapanes y Obleas', 'Cacahuates y Semillas', 'Bebidas', 'Snacks',
            ],
            'refaccionaria' => [
                'Frenos', 'Motor y Transmisión', 'Suspensión y Dirección', 'Sistema Eléctrico',
                'Carrocería y Accesorios', 'Aceites y Lubricantes', 'Herramientas', 'Filtros',
            ],
            'ferreteria' => [
                'Herramientas Manuales', 'Herramientas Eléctricas', 'Tornillería y Fijaciones',
                'Plomería', 'Electricidad', 'Pinturas y Acabados', 'Madera y Construcción', 'Cerrajería',
            ],
            'farmacia' => [
                'Medicamentos', 'Vitaminas y Suplementos', 'Higiene Personal',
                'Primeros Auxilios', 'Cuidado del Bebé', 'Cosméticos y Belleza', 'Ortopedia',
            ],
            'papeleria' => [
                'Útiles Escolares', 'Material de Oficina', 'Impresión y Copiado',
                'Arte y Manualidades', 'Tecnología', 'Libros y Revistas',
            ],
            'carniceria' => [
                'Res', 'Cerdo', 'Pollo', 'Mariscos y Pescado', 'Embutidos', 'Vísceras', 'Marinados',
            ],
            'panaderia' => [
                'Pan de Dulce', 'Pan de Sal', 'Pasteles y Tortas', 'Galletas', 'Bebidas', 'Insumos',
            ],
            'ropa' => [
                'Ropa de Hombre', 'Ropa de Mujer', 'Ropa de Niño', 'Calzado', 'Accesorios',
                'Ropa Deportiva', 'Lencería',
            ],
            'electronica' => [
                'Computadoras y Laptops', 'Celulares y Tablets', 'Accesorios y Cables',
                'Audio y Video', 'Impresoras', 'Componentes', 'Periféricos',
            ],
            default => ['General'],
        };
    }

    // Requerido por Filament para mostrar el nombre en el tenant switcher
    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $feature): bool
    {
        return $this->plan?->hasFeature($feature) ?? false;
    }
}
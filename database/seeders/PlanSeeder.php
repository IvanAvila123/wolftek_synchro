<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'         => 'Basico',
                'price'        => 499.00,
                'max_users'    => 4,
                'max_branches' => 1,
                'features'     => [
                    'pos',             // Punto de Venta (Cajas, Turnos)
                    'basic_inventory', // Productos, Categorías, Ajustes
                ],
            ],
            [
                'name'         => 'Profesional',
                'price'        => 899.00,
                'max_users'    => 10,
                'max_branches' => 1,
                'features'     => [
                    'pos',
                    'basic_inventory',
                    'suppliers',       // Proveedores y Compras
                    'customers',       // Clientes y fiado
                    'batches',         // Lotes y caducidades
                    'expenses',        // Control de gastos
                    'labels',          // Etiquetador
                    'scale',           // Báscula de precio
                    'online_catalog',  // Catálogo en línea
                    'advanced_reports',// Reportes avanzados
                ],
            ],
            [
                'name'         => 'Empresarial',
                'price'        => 2500.00,
                'max_users'    => -1, // -1 = ilimitado
                'max_branches' => -1, // -1 = ilimitado
                'features'     => [
                    'pos',
                    'basic_inventory',
                    'suppliers',
                    'customers',
                    'batches',
                    'expenses',
                    'labels',
                    'scale',
                    'online_catalog',
                    'advanced_reports',
                    'multi_branch',    // Multi-sucursal
                    'loyalty',         // Programa de lealtad
                    'api_access',      // Acceso API
                ],
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}

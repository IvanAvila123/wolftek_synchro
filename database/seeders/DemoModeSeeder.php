<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Datos de ejemplo para el modo demo (php artisan demo:reset).
 * Crea una tienda de abarrotes con su dueña (usuario demo), productos,
 * clientes con crédito, una caja abierta y dos semanas de ventas.
 */
class DemoModeSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--option' => 'permissions']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $plan = Plan::orderByDesc('price')->first();

        $user = User::create([
            'name' => 'Usuario Demo',
            'email' => config('demo.login'),
            'password' => config('demo.password'),
            'is_active' => true,
        ]);

        $store = Store::create([
            'user_id' => $user->id,
            'plan_id' => $plan?->id,
            'name' => 'Abarrotes La Flor',
            'business_type' => 'abarrotes',
            'rfc' => 'SIN RFC',
            'address' => 'Av. Juárez 120, Centro',
            'phone' => '5598042389',
        ]);
        $store->forceFill(['estatus' => 'activo'])->save();

        app(PermissionRegistrar::class)->setPermissionsTeamId($store->id);
        $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web', 'store_id' => $store->id]);
        $role->forceFill(['panel' => 'admin'])->save();
        $role->syncPermissions(Permission::all());
        $user->assignRole($role);

        $categorias = collect(['Abarrotes', 'Bebidas', 'Lácteos', 'Botanas', 'Limpieza', 'Panadería'])
            ->mapWithKeys(fn ($c) => [$c => Category::create(['store_id' => $store->id, 'name' => $c])]);

        // [nombre, categoría, costo, precio, existencias]
        $productos = [
            ['Coca-Cola 600 ml', 'Bebidas', 12, 18, 48], ['Agua Ciel 1 L', 'Bebidas', 8, 13, 60], ['Jugo Del Valle 1 L', 'Bebidas', 18, 27, 4],
            ['Leche Lala 1 L', 'Lácteos', 21, 28, 30], ['Yogurt Yoplait 1 kg', 'Lácteos', 34, 46, 3], ['Queso Oaxaca 250 g', 'Lácteos', 38, 55, 12],
            ['Sabritas Original 45 g', 'Botanas', 13, 19, 40], ['Doritos Nacho 62 g', 'Botanas', 14, 20, 2], ['Cacahuates Japoneses', 'Botanas', 9, 15, 25],
            ['Arroz Verde Valle 1 kg', 'Abarrotes', 24, 33, 22], ['Frijol negro 1 kg', 'Abarrotes', 28, 38, 18], ['Aceite 1-2-3 1 L', 'Abarrotes', 35, 47, 5],
            ['Pinol 1 L', 'Limpieza', 22, 32, 14], ['Cloralex 1 L', 'Limpieza', 16, 24, 20],
            ['Pan Bimbo grande', 'Panadería', 38, 52, 9], ['Mantecadas Bimbo', 'Panadería', 22, 31, 3],
        ];
        $products = collect($productos)->map(fn ($p, $i) => Product::create([
            'store_id' => $store->id,
            'category_id' => $categorias[$p[1]]->id,
            'name' => $p[0],
            'barcode' => '75010' . str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
            'price_buy' => $p[2],
            'price_sell' => $p[3],
            'stock' => $p[4],
            'stock_min' => 6,
            'unidad' => 'pieza',
            'has_scale' => false,
            'is_active' => true,
        ]));

        foreach ([['Doña Carmen', 320], ['Don Ramiro', 0], ['Lupita Hernández', 145], ['Taquería El Güero', 890]] as $i => [$nombre, $saldo]) {
            Customer::create([
                'store_id' => $store->id,
                'name' => $nombre,
                'phone' => '55' . (40000000 + $i * 1234567),
                'credit_limit' => 1500,
                'balance' => $saldo,
            ]);
        }

        $caja = CashRegister::create(['store_id' => $store->id, 'name' => 'Caja 1']);
        $turno = CashShift::create([
            'store_id' => $store->id,
            'cash_register_id' => $caja->id,
            'user_id' => $user->id,
            'opening_amount' => 500,
            'status' => 'open',
            'opened_at' => now()->startOfDay()->addHours(8),
        ]);

        // Dos semanas de ventas, con más movimiento en los días recientes.
        mt_srand(7);
        for ($d = 13; $d >= 0; $d--) {
            $ventas = mt_rand(6, 14) + intdiv(13 - $d, 2);
            for ($k = 0; $k < $ventas; $k++) {
                $hora = $d === 0 ? mt_rand(8, max(8, (int) now()->format('G'))) : mt_rand(8, 20);
                $at = now()->subDays($d)->setTime($hora, mt_rand(0, 59));
                $sale = Sale::create([
                    'store_id' => $store->id,
                    'cash_shift_id' => $turno->id,
                    'user_id' => $user->id,
                    'total' => 0,
                    'discount' => 0,
                    'payment_method' => ['cash', 'card', 'cash', 'transfer'][mt_rand(0, 3)],
                    'status' => 'completed',
                ]);

                $total = 0;
                foreach ($products->random(mt_rand(1, 4)) as $product) {
                    $cantidad = mt_rand(1, 3);
                    $subtotal = $cantidad * $product->price_sell;
                    $total += $subtotal;
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $cantidad,
                        'price' => $product->price_sell,
                        'discount' => 0,
                        'subtotal' => $subtotal,
                    ]);
                }

                $sale->forceFill(['total' => $total, 'created_at' => $at, 'updated_at' => $at])->saveQuietly();
            }
        }
    }
}

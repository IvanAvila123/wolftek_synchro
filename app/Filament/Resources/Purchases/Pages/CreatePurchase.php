<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Crear Nueva Compra';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Le mandamos un 0 temporal al inicio
        $data['total'] = 0; 
        
        return $data;
    }

    // Quitamos la función "BeforeCreate" y hacemos toda la magia aquí,
    // DESPUÉS de que Filament ya guardó las filas en la base de datos.
    protected function afterCreate(): void
    {
        $purchase = $this->record;
        $granTotal = 0; // Inicializamos nuestra calculadora

        // Recorremos los productos que ya se guardaron
        foreach ($purchase->items as $item) {
            
            // 1. Sumamos el subtotal de esta fila a nuestro Gran Total
            $granTotal += $item->subtotal;

            // 2. Le sumamos la mercancía a tu tienda
            $producto = Product::find($item->product_id);
            if ($producto) {
                $producto->increment('stock', $item->quantity);
                
                // Actualizamos el costo de compra si es mayor a cero
                if ($item->price_buy > 0) {
                    $producto->update(['price_buy' => $item->price_buy]);
                }
            }
        }

        // 3. AHORA SÍ: Actualizamos la factura original con la suma real
        $purchase->update(['total' => $granTotal]);

        // 4. Si la compra fue a crédito, le aumentamos la deuda al proveedor
        // (Nota: Usamos 'pendiente' porque así lo llamaste en tu formulario)
        if ($purchase->estatus === 'pendiente') {
            $proveedor = $purchase->supplier;
            if ($proveedor) {
                $proveedor->increment('balance', $granTotal);
            }
        }
    }
    
    // Al terminar, mandamos al dueño de regreso a la tabla principal
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

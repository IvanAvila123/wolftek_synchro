<?php

namespace App\Filament\Cashier\Pages;

use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PuntoDeVenta extends Page
{
    protected string $view = 'filament.cashier.pages.punto-de-venta';
    protected static ?string $navegationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $title = 'Punto de Venta';
    protected static ?string $slug = 'pos';

    protected static ?int $navegationSort = 1;

    public string $barcode = '';
    public array $cart = [];
    public float $total = 0.00;
    public string $paymentMethod = 'cash';
    public string $customerId = '';
    public string $montoRecibido = '';
    public float $cambio = 0.00;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('registrar_gasto')
                ->label('💸 Retirar Efectivo (Gasto)')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\TextInput::make('concept')
                        ->label('Concepto o Motivo del retiro')
                        ->placeholder('Ej. Pago proveedor de agua, Compra de bolsas...')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Monto a retirar de la caja')
                        ->numeric()
                        ->prefix('$')
                        ->required()
                        ->minValue(0.1),
                ])
                ->modalHeading('Registrar Salida de Efectivo')
                ->modalDescription('Este dinero se restará de tu efectivo esperado en el corte de caja.')
                ->modalSubmitActionLabel('Confirmar Retiro')
                ->action(function (array $data) {
                    $user = auth()->user();
                    $storeId = filament()->getTenant()->id;
                    $shift = \App\Models\CashShift::where('user_id', $user->id)
                                ->where('status', 'open')
                                ->first();

                    if (!$shift) {
                        Notification::make()->title('Error')->body('Abre tu turno primero.')->danger()->send();
                        return;
                    }

                    // Registramos el gasto en la base de datos
                    $gasto = \App\Models\Expense::create([
                        'store_id'      => $storeId,
                        'cash_shift_id' => $shift->id,
                        'user_id'       => $user->id,
                        'concept'       => $data['concept'],
                        'amount'        => $data['amount'],
                    ]);

                    // Mostramos la notificación CON EL BOTÓN DE IMPRIMIR
                    \Filament\Notifications\Notification::make()
                        ->title('Gasto registrado')
                        ->body('Se han retirado $' . number_format($data['amount'], 2) . ' de la caja.')
                        ->success()
                        ->actions([
                            Action::make('imprimir')
                                ->label('🖨️ Imprimir Vale')
                                ->button()
                                ->color('danger')
                                ->url(route('ticket.gasto', $gasto->id), shouldOpenInNewTab: true),
                        ])
                        ->send();
                }),
        ];
    }

    public function updatedMontoRecibido()
    {
        $this->calcularCambio();
    }

    // Se ejecuta si cambian de Efectivo a Tarjeta (limpiamos el monto y cambio)
    public function updatedPaymentMethod()
    {
        $this->montoRecibido = '';
        $this->cambio = 0.00;
    }

    public function calcularCambio()
    {
        $monto = (float) $this->montoRecibido;
        if ($monto >= $this->total) {
            $this->cambio = $monto - $this->total;
        } else {
            $this->cambio = 0.00;
        }
    }

    /**
     * Esta función se disparará cuando la pistola escáner 
     * termine de leer el código y mande el "Enter" automático.
     */

    public function buscarProducto()
    {
        if (empty($this->barcode)) return;

        $storeId = filament()->getTenant()->id;

        // Buscamos el producto en la base de datos de esta tienda
        $product = Product::where('store_id', $storeId)
            ->where('barcode', $this->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            Notification::make()
                ->title('Producto no encontrado')
                ->danger()
                ->send();
            
            $this->barcode = ''; // Limpiamos para intentar de nuevo
            return;
        }

        // Si existe, lo mandamos al carrito
        $this->agregarAlCarrito($product);
        
        // Limpiamos el input para el siguiente escaneo inmediato
        $this->barcode = ''; 
    }

    public function agregarAlCarrito(Product $product)
    {
        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['quantity']++;
            $this->cart[$product->id]['subtotal'] = $this->recalcularSubtotal($this->cart[$product->id]);
        } else {
            $precio     = (float) $product->price_sell;
            $promo      = $product->promocionActivaFefo();
            $resultado  = $promo
                ? $promo->calcularSubtotal(1, $precio)
                : ['price_unit' => $precio, 'subtotal' => $precio, 'ahorro' => 0, 'label' => null,
                   'promo_id' => null, 'tipo' => null, 'cantidad_paga' => null, 'cantidad_lleva' => null];

            $this->cart[$product->id] = [
                'id'             => $product->id,
                'name'           => $product->name,
                'price'          => $precio,
                'quantity'       => 1,
                'subtotal'       => $resultado['subtotal'],
                // Datos de promoción (null si no aplica)
                'promo_id'       => $resultado['promo_id'],
                'promo_tipo'     => $resultado['tipo'],
                'promo_paga'     => $resultado['cantidad_paga'],
                'promo_lleva'    => $resultado['cantidad_lleva'],
                'promo_label'    => $resultado['label'],
                'ahorro'         => $resultado['ahorro'],
            ];
        }

        $this->calcularTotal();
    }

    /**
     * Recalcula el subtotal de un ítem del carrito considerando su promoción.
     */
    private function recalcularSubtotal(array $item): float
    {
        $qty   = (float) $item['quantity'];
        $price = (float) $item['price'];

        if (! $item['promo_tipo']) {
            return round($qty * $price, 2);
        }

        if ($item['promo_tipo'] === 'nxm') {
            $paga  = (int) $item['promo_paga'];
            $lleva = (int) $item['promo_lleva'];
            if ($lleva > 0 && $paga > 0) {
                $bloques = (int) floor($qty / $lleva);
                $resto   = fmod($qty, $lleva);
                return round(($bloques * $paga + $resto) * $price, 2);
            }
        }

        // porcentaje y precio_fijo: usar promo para recalcular desde el modelo
        if ($item['promo_id']) {
            $promo = \App\Models\Promotion::find($item['promo_id']);
            if ($promo && $promo->estaVigente()) {
                return $promo->calcularSubtotal($qty, $price)['subtotal'];
            }
        }

        return round($qty * $price, 2);
    }

    public function actualizarCantidad($productId, $cantidad)
    {
        $cantidad = (float) $cantidad;

        if ($cantidad <= 0) {
            $this->eliminarDelCarrito($productId);
            return;
        }

        $this->cart[$productId]['quantity'] = $cantidad;
        $this->cart[$productId]['subtotal'] = $this->recalcularSubtotal($this->cart[$productId]);

        $this->calcularTotal();
    }

    public function eliminarDelCarrito($productId)
    {
        unset($this->cart[$productId]);
        $this->calcularTotal();
    }

    public function vaciarCarrito()
    {
        $this->cart = [];
        $this->calcularTotal();
    }

    public function calcularTotal()
    {
        $this->total = collect($this->cart)->sum('subtotal');
        $this->calcularCambio();
    }

    // Dejaremos la función de cobrar pendiente para el siguiente paso
    public function procesarVenta()
    {
        if (empty($this->cart)) return;

        // Validaciones de pago
        if ($this->paymentMethod === 'cash') {
            $recibido = (float) $this->montoRecibido;
            if ($recibido < $this->total) {
                Notification::make()->title('Monto insuficiente')->danger()->send();
                return;
            }
        }

        // 👇 VALIDACIONES DE CRÉDITO
        if ($this->paymentMethod === 'credit') {
            if (empty($this->customerId)) {
                Notification::make()->title('Selecciona un cliente')->body('Debes elegir a quién le vas a fiar.')->warning()->send();
                return;
            }

            $cliente = \App\Models\Customer::find($this->customerId);
            
            // Si tiene límite de crédito (> 0) revisamos que no se pase
            if ($cliente->credit_limit > 0 && ($cliente->balance + $this->total) > $cliente->credit_limit) {
                Notification::make()->title('Límite excedido')
                    ->body('Esta venta supera el límite de crédito de $' . number_format($cliente->credit_limit, 2))
                    ->danger()->send();
                return;
            }
        }

        $user = auth()->user();
        $storeId = filament()->getTenant()->id;
        $shift = \App\Models\CashShift::where('user_id', $user->id)->where('status', 'open')->first();

        if (!$shift) {
            Notification::make()->title('Error')->body('Caja cerrada.')->danger()->send();
            return;
        }

        $saleId = null; 

        \Illuminate\Support\Facades\DB::transaction(function () use ($storeId, $user, $shift, &$saleId) {
            
            // A) Crear la venta principal
            $sale = \App\Models\Sale::create([
                'store_id'       => $storeId,
                'cash_shift_id'  => $shift->id,
                'user_id'        => $user->id,
                // Si es a crédito, guardamos quién fue
                'customer_id'    => $this->paymentMethod === 'credit' ? $this->customerId : null,
                'total'          => $this->total,
                'payment_method' => $this->paymentMethod,
                'status'         => 'completed',
            ]);

            $saleId = $sale->id; 

            // B) Registrar productos y descontar stock
            foreach ($this->cart as $item) {
                \App\Models\SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'subtotal'   => $item['subtotal'],
                ]);
                \App\Models\Product::where('id', $item['id'])->decrement('stock', $item['quantity']);
            }

            // 👇 C) SI ES A CRÉDITO, REGISTRAMOS LA DEUDA
            if ($this->paymentMethod === 'credit') {
                \App\Models\CreditSale::create([
                    'customer_id' => $this->customerId,
                    'sale_id'     => $sale->id,
                    'amount'      => $this->total,
                    'paid_amount' => 0,
                    'status'      => 'pending',
                ]);
                
                // Le sumamos la deuda al total del cliente
                \App\Models\Customer::where('id', $this->customerId)->increment('balance', $this->total);
            }
        });

        // Limpiamos todo
        $this->vaciarCarrito();
        $this->montoRecibido = '';
        $this->cambio = 0.00;
        $this->customerId = ''; // Limpiamos el cliente

        Notification::make()
            ->title('¡Venta cobrada!')
            ->success()
            ->actions([
                Action::make('imprimir')
                    ->label('🖨️ Imprimir Ticket')
                    ->button()
                    ->color('success')
                    ->url(route('ticket.imprimir', $saleId), shouldOpenInNewTab: true),
            ])
            ->send();
    }
}

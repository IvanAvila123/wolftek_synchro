<?php

namespace App\Filament\Cashier\Pages;

use App\Models\CashShift;
use App\Models\CreditPayment;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Abonos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Ventas';
    protected string $view = 'filament.cashier.pages.abonos';
    protected static ?string $title = 'Cobro de Créditos (Abonos)';
    protected static ?int $navigationSort = 3;

    public string $customerId = '';
    public string $monto = '';
    public string $paymentMethod = 'cash';
    

    public function procesarAbono()
    {
        $montoPago = (float) $this->monto;

        if (empty($this->customerId) || $montoPago <= 0) {
            Notification::make()->title('Ingresa un monto válido')->warning()->send();
            return;
        }

        $user = auth()->user();
        $storeId = filament()->getTenant()->id;
        
        // 1. Validar que la caja esté abierta
        $shift = CashShift::where('user_id', $user->id)->where('status', 'open')->first();

        if (!$shift) {
            Notification::make()->title('Abre tu turno primero')->danger()->send();
            return;
        }

        // 2. Validar al cliente y su deuda
        $cliente = Customer::where('id', $this->customerId)->where('store_id', $storeId)->first();
        
        if ($montoPago > $cliente->balance) {
            Notification::make()->title('Error')->body('El cliente solo debe $' . number_format($cliente->balance, 2))->danger()->send();
            return;
        }

        $pagoId = null; // Creamos esta variable temporal

        DB::transaction(function() use ($storeId, $shift, $cliente, $montoPago, &$pagoId) {
            
            // Registramos el pago y capturamos su ID
            $pago = CreditPayment::create([
                'store_id'      => $storeId,
                'cash_shift_id' => $shift->id,
                'customer_id'   => $cliente->id,
                'amount'        => $montoPago,
                'payment_method'=> $this->paymentMethod,
            ]);

            $pagoId = $pago->id; // Guardamos el ID

            // Le restamos la deuda a su cuenta
            $cliente->decrement('balance', $montoPago);
        });

        // Limpiar pantalla
        $this->customerId = '';
        $this->monto = '';
        $this->paymentMethod = 'cash';

        // Notificación con el botón mágico
        Notification::make()
            ->title('¡Abono registrado con éxito!')
            ->success()
            ->actions([
                Action::make('imprimir')
                    ->label('🖨️ Imprimir Comprobante')
                    ->button()
                    ->color('success')
                    ->url(route('abono.imprimir', $pagoId), shouldOpenInNewTab: true),
            ])
            ->send();
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Cashier\Widgets\LatestCreditPayments::class,
        ];
    }
}

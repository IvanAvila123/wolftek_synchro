<?php

namespace App\Filament\Cashier\Pages;

use App\Models\CashShift;
use App\Models\Sale;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class CorteDeCaja extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected string $view = 'filament.cashier.pages.corte-de-caja';
    protected static ?string $title = 'Corte de Caja';
    protected static ?string $slug = 'corte-caja';

    // Lo ponemos en el menú justo debajo del Punto de Venta
    protected static ?int $navigationSort = 2;

    public ?array $data = [];
    public ?CashShift $shift = null;
    public array $resumen = [];

    public function mount(): void
    {
        $user = auth()->user();

        // Buscamos el turno abierto actual
        $this->shift = CashShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$this->shift) {
            Notification::make()
                ->title('No tienes un turno abierto')
                ->warning()
                ->send();
            redirect()->route('filament.cashier.pages.pos', ['tenant' => filament()->getTenant()->id]);
            return;
        }

        $this->calcularResumen();
        $this->form->fill();
    }

    public function calcularResumen()
    {
        $ventas = \App\Models\Sale::where('cash_shift_id', $this->shift->id)->get();
        $ventasEfectivo = $ventas->where('payment_method', 'cash')->sum('total');
        $ventasTarjeta = $ventas->where('payment_method', 'card')->sum('total');
        $ventasTransferencia = $ventas->where('payment_method', 'transfer')->sum('total');
        $ventasCredito = $ventas->where('payment_method', 'credit')->sum('total');

        // Traemos todos los abonos de este turno
        $abonos = \App\Models\CreditPayment::where('cash_shift_id', $this->shift->id)->get();
        
        // Separamos los abonos por método
        $abonosEfectivo = $abonos->where('payment_method', 'cash')->sum('amount');
        $abonosTarjeta = $abonos->where('payment_method', 'card')->sum('amount');
        $abonosTransferencia = $abonos->where('payment_method', 'transfer')->sum('amount');

        $gastosEfectivo = \App\Models\Expense::where('cash_shift_id', $this->shift->id)->sum('amount');

        $fondoInicial = $this->shift->opening_amount;
        
        // El esperado en caja física SOLO suma el efectivo
        $esperadoEnCaja = $fondoInicial + $ventasEfectivo + $abonosEfectivo - $gastosEfectivo; 

        $this->resumen = [
            'fondo_inicial' => $fondoInicial,
            'ventas_efectivo' => $ventasEfectivo,
            'ventas_tarjeta' => $ventasTarjeta,
            'ventas_transferencia' => $ventasTransferencia,
            'ventas_credito' => $ventasCredito,
            'abonos_efectivo' => $abonosEfectivo,
            'abonos_tarjeta' => $abonosTarjeta, // <-- NUEVO
            'abonos_transferencia' => $abonosTransferencia, // <-- NUEVO
            'gastos_efectivo' => $gastosEfectivo,
            'total_ventas' => $ventas->sum('total'),
            'esperado_en_caja' => $esperadoEnCaja,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('closing_amount')
                    ->label('Efectivo real en la gaveta')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->live(onBlur: true)
                    ->helperText('Cuenta los billetes y monedas físicos.'),
            ])
            ->statePath('data');
    }

    public function cerrarCaja()
    {
        $data = $this->form->getState();

        // Cerramos el turno guardando la cantidad final y la hora
        $this->shift->update([
            'closing_amount' => $data['closing_amount'],
            'status'         => 'closed',
            'closed_at'      => now(),
        ]);

        Notification::make()
            ->title('Turno cerrado exitosamente')
            ->body('El corte de caja ha sido guardado.')
            ->success()
            ->send();

        // Al cerrar, lo mandamos al dashboard (el cual, gracias al middleware, 
        // lo bloqueará si intenta ir a vender sin abrir caja otra vez).
        return redirect()->route('filament.cashier.pages.pos', ['tenant' => filament()->getTenant()->id]);
    }
}

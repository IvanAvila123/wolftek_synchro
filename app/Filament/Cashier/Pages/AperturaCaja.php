<?php

namespace App\Filament\Cashier\Pages;

use App\Models\CashRegister;
use App\Models\CashShift;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class AperturaCaja extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cash'; 
    protected string $view = 'filament.cashier.pages.apertura-caja';
    protected static ?string $title = 'Apertura de Turno';
    protected static ?string $slug = 'apertura-caja';
    
    // Ocultamos esta página del menú lateral izquierdo para que no estorbe
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cash_register_id')
                    ->label('Caja Registradora')
                    ->options(function () {
                        // Solo mostramos las cajas que pertenecen a la tienda actual
                        $storeId = filament()->getTenant()->id;
                        return CashRegister::where('store_id', $storeId)->pluck('name', 'id');
                    })
                    ->required(),
                TextInput::make('opening_amount')
                    ->label('Fondo de caja inicial (Efectivo base)')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->minValue(0)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function abrirCaja()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            CashShift::create([
                'store_id'         => filament()->getTenant()->id, // Asignamos la tienda actual al turno
                'cash_register_id' => $data['cash_register_id'],
                'user_id'          => auth()->id(),
                'opening_amount'   => $data['opening_amount'],
                'status'           => 'open',
                'opened_at'        => now(),
            ]);
        });

        Notification::make()
            ->title('¡Turno iniciado correctamente!')
            ->success()
            ->send();

        return redirect()->route('filament.cashier.pages.pos', [
            'tenant' => filament()->getTenant()->id
        ]);
    }
}